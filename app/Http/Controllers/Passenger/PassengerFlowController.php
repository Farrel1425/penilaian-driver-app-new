<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Passenger\StorePassengerNameRequest;
use App\Http\Requests\Passenger\StoreRatingRequest;
use App\Models\Driver;
use App\Models\Question;
use App\Models\Rating;
use App\Models\RatingAnswer;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PassengerFlowController extends Controller
{
    public function vehicle(string $vehicleToken): View
    {
        return view('passenger.vehicle', ['vehicle' => $this->activeVehicle($vehicleToken)]);
    }

    public function drivers(string $vehicleToken): View
    {
        $vehicle = $this->activeVehicle($vehicleToken);
        $drivers = Driver::query()
            ->where('branch_id', $vehicle->branch_id)
            ->active()
            ->orderBy('full_name')
            ->get();

        $driverScores = RatingAnswer::query()
            ->whereHas('question', fn ($query) => $query
                ->where('target_type', Question::TARGET_DRIVER)
                ->where('answer_type', Question::TYPE_RATING))
            ->whereHas('rating', fn ($query) => $query
                ->where('branch_id', $vehicle->branch_id)
                ->whereIn('driver_id', $drivers->pluck('id')))
            ->with('rating:id,driver_id')
            ->get()
            ->groupBy(fn (RatingAnswer $answer) => $answer->rating?->driver_id)
            ->map(function ($answers): ?float {
                $scores = $answers->map(fn (RatingAnswer $answer) => $answer->answer_value[0] ?? null)
                    ->filter(fn ($value) => in_array((int) $value, [1, 2, 3, 4, 5], true));

                return $scores->isEmpty() ? null : round($scores->avg(), 1);
            });

        $drivers->each(fn (Driver $driver) => $driver->setAttribute('passenger_average_rating', $driverScores->get($driver->id)));

        return view('passenger.drivers', compact('vehicle', 'drivers'));
    }

    public function driver(string $vehicleToken, Driver $driver): View
    {
        $vehicle = $this->activeVehicle($vehicleToken);
        $this->ensureSelectableDriver($vehicle, $driver);

        return view('passenger.driver-detail', compact('vehicle', 'driver'));
    }

    public function assessor(string $vehicleToken, Driver $driver): View
    {
        $vehicle = $this->activeVehicle($vehicleToken);
        $this->ensureSelectableDriver($vehicle, $driver);
        $passengerName = session($this->passengerNameSessionKey($vehicle, $driver));

        return view('passenger.assessor', compact('vehicle', 'driver', 'passengerName'));
    }

    public function storeAssessor(StorePassengerNameRequest $request, string $vehicleToken, Driver $driver): RedirectResponse
    {
        $vehicle = $this->activeVehicle($vehicleToken);
        $this->ensureSelectableDriver($vehicle, $driver);

        session()->forget($this->passengerSubmissionTokenSessionKey($vehicle, $driver));
        session()->put(
            $this->passengerNameSessionKey($vehicle, $driver),
            trim($request->validated('passenger_name')),
        );

        return redirect()->route('passenger.rating.assessment', [$vehicle->qr_token, $driver]);
    }

    public function assessment(string $vehicleToken, Driver $driver): View|RedirectResponse
    {
        $vehicle = $this->activeVehicle($vehicleToken);
        $this->ensureSelectableDriver($vehicle, $driver);
        $passengerName = session($this->passengerNameSessionKey($vehicle, $driver));

        if (! is_string($passengerName) || trim($passengerName) === '') {
            return redirect()
                ->route('passenger.rating.assessor', [$vehicle->qr_token, $driver])
                ->with('error', 'Silakan isi nama Anda sebelum memberikan penilaian.');
        }

        $submissionToken = session($this->passengerSubmissionTokenSessionKey($vehicle, $driver));

        if (! is_string($submissionToken)) {
            $submissionToken = (string) Str::uuid();
            session()->put($this->passengerSubmissionTokenSessionKey($vehicle, $driver), $submissionToken);
        }

        $questions = Question::query()
            ->with('options')
            ->active()
            ->ordered()
            ->get()
            ->groupBy('target_type');

        return view('passenger.assessment', compact('vehicle', 'driver', 'questions', 'passengerName', 'submissionToken'));
    }

    public function submit(StoreRatingRequest $request, string $vehicleToken, Driver $driver): RedirectResponse
    {
        $vehicle = $this->activeVehicle($vehicleToken);
        $this->ensureSelectableDriver($vehicle, $driver);
        $questions = Question::query()->with('options')->active()->ordered()->get();
        $answers = $request->validatedAnswers($questions);
        $passengerName = trim($request->validated('passenger_name'));
        $submissionToken = $request->string('submission_token')->toString();
        abort_unless(
            $submissionToken !== '' && hash_equals((string) session($this->passengerSubmissionTokenSessionKey($vehicle, $driver)), $submissionToken),
            419,
        );

        $submissionKey = "passenger-rating-submission:{$submissionToken}";

        if (! Cache::add($submissionKey, 'processing', now()->addMinutes(5))) {
            $existingRatingId = Cache::get($submissionKey);

            if (is_int($existingRatingId) || ctype_digit((string) $existingRatingId)) {
                return redirect()->route('passenger.rating.success', [$vehicle->qr_token, $existingRatingId]);
            }

            return redirect()
                ->route('passenger.rating.assessment', [$vehicle->qr_token, $driver])
                ->with('error', 'Penilaian sedang dikirim. Mohon tunggu sebentar.');
        }

        try {
            $rating = DB::transaction(function () use ($vehicle, $driver, $answers, $passengerName): Rating {
                $rating = Rating::query()->create([
                    'branch_id' => $vehicle->branch_id,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driver->id,
                    'passenger_name' => $passengerName,
                    'submitted_at' => now(),
                ]);

                foreach ($answers as $answer) {
                    $rating->answers()->create($answer);
                }

                return $rating;
            });
        } catch (\Throwable $exception) {
            Cache::forget($submissionKey);

            throw $exception;
        }

        Cache::put($submissionKey, $rating->id, now()->addDay());
        session()->forget($this->passengerNameSessionKey($vehicle, $driver));

        return redirect()->route('passenger.rating.success', [$vehicle->qr_token, $rating]);
    }

    public function success(string $vehicleToken, Rating $rating): View
    {
        $vehicle = $this->activeVehicle($vehicleToken);

        abort_unless($rating->vehicle_id === $vehicle->id, 404);

        return view('passenger.success', compact('vehicle', 'rating'));
    }

    private function activeVehicle(string $vehicleToken): Vehicle
    {
        $vehicle = Vehicle::query()->with('branch')->where('qr_token', $vehicleToken)->firstOrFail();

        if ($vehicle->status !== Vehicle::STATUS_ACTIVE) {
            throw new HttpException(403, 'Kendaraan tidak aktif.');
        }

        return $vehicle;
    }

    private function ensureSelectableDriver(Vehicle $vehicle, Driver $driver): void
    {
        abort_unless($driver->status === Driver::STATUS_ACTIVE, 404);
        abort_unless($driver->branch_id === $vehicle->branch_id, 404);
    }

    private function passengerNameSessionKey(Vehicle $vehicle, Driver $driver): string
    {
        return "passenger_name_{$vehicle->id}_{$driver->id}";
    }

    private function passengerSubmissionTokenSessionKey(Vehicle $vehicle, Driver $driver): string
    {
        return "passenger_submission_token_{$vehicle->id}_{$driver->id}";
    }
}
