<?php

namespace App\Services\Admin;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\DriverAttendance;
use App\Models\Question;
use App\Models\Rating;
use App\Models\RatingAnswer;
use App\Models\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationalMonitoringService
{
    public function period(?string $value): CarbonImmutable
    {
        $timezone = config('app.display_timezone', 'Asia/Makassar');

        if ($value && preg_match('/^\d{4}-\d{2}$/', $value)) {
            return CarbonImmutable::parse($value.'-01', $timezone)->startOfMonth();
        }

        return CarbonImmutable::now($timezone)->startOfMonth();
    }

    public function workingDays(CarbonImmutable $period): int
    {
        $workingDays = 0;

        for ($date = $period->startOfMonth(); $date->lte($period->endOfMonth()); $date = $date->addDay()) {
            if ($date->isWeekday()) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    public function branchRows(CarbonImmutable $period, ?int $branchId = null): Collection
    {
        $branches = Branch::query()
            ->active()
            ->when($branchId, fn (Builder $query) => $query->whereKey($branchId))
            ->with([
                'drivers' => fn ($query) => $this->eligibleDrivers($query)->orderBy('full_name'),
                'vehicles' => fn ($query) => $query->active()->orderBy('police_number'),
            ])
            ->withCount(['vehicles' => fn ($query) => $query->active()])
            ->orderBy('name')
            ->get();

        $driverIds = $branches->flatMap->drivers->pluck('id');
        $vehicleIds = $branches->flatMap->vehicles->pluck('id');
        $attendances = $this->attendances($period, $driverIds)->keyBy('driver_id');
        $ratings = $this->ratings($period, $driverIds)->groupBy('driver_id');
        $vehicleRatings = $this->vehicleRatings($period, $vehicleIds)->groupBy('vehicle_id');

        $workingDays = $this->workingDays($period);

        return $branches->map(function (Branch $branch) use ($attendances, $ratings, $vehicleRatings, $workingDays) {
            $driverRows = $branch->drivers->map(fn (Driver $driver) => $this->driverRow(
                $driver,
                $ratings->get($driver->id, collect()),
                $attendances->get($driver->id),
                $workingDays,
            ));
            $completed = $driverRows->where('is_complete', true)->count();
            $attendanceScores = $driverRows->pluck('attendance_score')->filter(fn ($score) => $score !== null);
            $vehicleRows = $branch->vehicles->map(fn (Vehicle $vehicle) => $this->vehicleRow(
                $vehicle,
                $vehicleRatings->get($vehicle->id, collect()),
            ));
            $vehicleScores = $vehicleRows->pluck('vehicle_score')->filter(fn ($score) => $score !== null);
            $vehiclesRated = $vehicleRows->where('has_ratings', true)->count();

            return [
                'branch' => $branch,
                'vehicles' => $branch->vehicles_count,
                'drivers' => $driverRows->count(),
                'completed' => $completed,
                'attendance_average' => $attendanceScores->isEmpty() ? null : round($attendanceScores->avg(), 2),
                'is_complete' => $driverRows->isNotEmpty() && $completed === $driverRows->count(),
                'vehicles_rated' => $vehiclesRated,
                'vehicle_rating_count' => $vehicleRows->sum('rating_count'),
                'vehicle_average' => $vehicleScores->isEmpty() ? null : round($vehicleScores->avg(), 2),
                'is_vehicle_complete' => $vehicleRows->isNotEmpty() && $vehiclesRated === $vehicleRows->count(),
            ];
        });
    }

    public function driverRows(Branch $branch, CarbonImmutable $period): Collection
    {
        $drivers = $this->eligibleDrivers($branch->drivers()->getQuery())
            ->with('employeeCategory')
            ->orderBy('full_name')
            ->get();
        $driverIds = $drivers->pluck('id');
        $attendances = $this->attendances($period, $driverIds)->keyBy('driver_id');
        $ratings = $this->ratings($period, $driverIds)->groupBy('driver_id');
        $workingDays = $this->workingDays($period);

        return $drivers->map(fn (Driver $driver) => $this->driverRow(
            $driver,
            $ratings->get($driver->id, collect()),
            $attendances->get($driver->id),
            $workingDays,
        ));
    }

    public function driverDetail(Branch $branch, Driver $driver, CarbonImmutable $period): array
    {
        $ratings = $this->ratings($period, collect([$driver->id]))->sortByDesc('submitted_at')->values();
        $attendance = $this->attendances($period, collect([$driver->id]))->first();
        $row = $this->driverRow($driver, $ratings, $attendance, $this->workingDays($period));
        $row['question_breakdown'] = $this->questionBreakdown($ratings, Question::TARGET_DRIVER);
        $row['vehicle_score'] = $this->weightedScore($ratings, Question::TARGET_VEHICLE);
        $row['comments'] = $ratings->flatMap->answers
            ->filter(fn (RatingAnswer $answer) => filled($answer->answer_text))
            ->sortByDesc('created_at')
            ->values();
        $row['branch'] = $branch;

        return $row;
    }

    public function vehicleRows(Branch $branch, CarbonImmutable $period, ?int $driverId = null): Collection
    {
        $vehicles = $branch->vehicles()->active()->orderBy('police_number')->get();
        $ratings = $this->vehicleRatings($period, $vehicles->pluck('id'), $driverId)->groupBy('vehicle_id');

        return $vehicles
            ->map(fn (Vehicle $vehicle) => $this->vehicleRow($vehicle, $ratings->get($vehicle->id, collect())))
            ->when($driverId, fn (Collection $rows) => $rows->where('has_ratings', true))
            ->values();
    }

    public function vehicleDetail(Branch $branch, Vehicle $vehicle, CarbonImmutable $period): array
    {
        $ratings = $this->vehicleRatings($period, collect([$vehicle->id]))->sortByDesc('submitted_at')->values();
        $row = $this->vehicleRow($vehicle, $ratings);
        $row['question_breakdown'] = $this->questionBreakdown($ratings, Question::TARGET_VEHICLE);
        $row['comments'] = $ratings->flatMap->answers
            ->filter(fn (RatingAnswer $answer) => filled($answer->answer_text))
            ->sortByDesc('created_at')
            ->values();
        $row['branch'] = $branch;

        return $row;
    }

    public function reportRows(Branch $branch, CarbonImmutable $period): array
    {
        $rows = $this->driverRows($branch, $period);
        $questions = Question::query()
            ->where('target_type', Question::TARGET_DRIVER)
            ->where('answer_type', Question::TYPE_RATING)
            ->active()
            ->ordered()
            ->get();
        $driverIndicators = $this->reportIndicators($questions);
        $vehicleRows = $this->vehicleRows($branch, $period);
        $vehicleQuestions = Question::query()
            ->where('target_type', Question::TARGET_VEHICLE)
            ->where('answer_type', Question::TYPE_RATING)
            ->active()
            ->ordered()
            ->get();
        $vehicleIndicators = $this->reportIndicators($vehicleQuestions);

        return [
            'branch' => $branch,
            'questions' => $questions,
            'driver_indicators' => $driverIndicators,
            'rows' => $rows->map(function (array $row) use ($driverIndicators) {
                $row['report_scores'] = $this->indicatorReportScores(
                    $row['ratings'],
                    Question::TARGET_DRIVER,
                    $driverIndicators,
                );

                return $row;
            }),
            'vehicle_questions' => $vehicleQuestions,
            'vehicle_indicators' => $vehicleIndicators,
            'vehicle_rows' => $vehicleRows->map(function (array $row) use ($vehicleIndicators) {
                $row['report_scores'] = $this->indicatorReportScores(
                    $row['ratings'],
                    Question::TARGET_VEHICLE,
                    $vehicleIndicators,
                );

                return $row;
            }),
        ];
    }

    public function driverReport(Branch $branch, Driver $driver, CarbonImmutable $period): array
    {
        return [
            'type' => 'driver',
            'branch' => $branch,
            'period' => $period,
            'detail' => $this->driverDetail($branch, $driver, $period),
            'related_vehicles' => $this->vehicleRows($branch, $period, $driver->id),
        ];
    }

    public function vehicleReport(Branch $branch, Vehicle $vehicle, CarbonImmutable $period): array
    {
        return [
            'type' => 'vehicle',
            'branch' => $branch,
            'period' => $period,
            'detail' => $this->vehicleDetail($branch, $vehicle, $period),
        ];
    }

    private function driverRow(Driver $driver, Collection $ratings, ?DriverAttendance $attendance, int $workingDays): array
    {
        $ratings = $ratings->sortByDesc('submitted_at')->values();
        $driverScore = $this->weightedScore($ratings, Question::TARGET_DRIVER);
        $attendanceScore = $attendance?->score();

        return [
            'driver' => $driver,
            'ratings' => $ratings,
            'rating_count' => $ratings->count(),
            'vehicle' => $ratings->first()?->vehicle,
            'driver_score' => $driverScore,
            'attendance' => $attendance,
            'attendance_score' => $attendanceScore,
            'working_days' => $workingDays,
            'final_score' => $driverScore !== null && $attendanceScore !== null
                ? round(($driverScore * 0.9) + ($attendanceScore * 0.1), 2)
                : null,
            'is_complete' => $attendance !== null && $attendance->totalDays() === $workingDays,
        ];
    }

    private function vehicleRow(Vehicle $vehicle, Collection $ratings): array
    {
        $ratings = $ratings->sortByDesc('submitted_at')->values();
        $drivers = $ratings->pluck('driver')->filter()->unique('id')->values();

        return [
            'vehicle' => $vehicle,
            'ratings' => $ratings,
            'rating_count' => $ratings->count(),
            'driver_count' => $drivers->count(),
            'drivers' => $drivers,
            'latest_rating' => $ratings->first(),
            'vehicle_score' => $this->weightedScore($ratings, Question::TARGET_VEHICLE),
            'has_ratings' => $ratings->isNotEmpty(),
        ];
    }

    private function weightedScore(Collection $ratings, string $targetType): ?float
    {
        $breakdown = $this->questionBreakdown($ratings, $targetType);
        $weight = $breakdown->sum('weight');

        if ($weight <= 0) {
            return null;
        }

        return round(($breakdown->sum('contribution') / $weight) * 100, 2);
    }

    private function questionBreakdown(Collection $ratings, string $targetType): Collection
    {
        return $ratings->flatMap->answers
            ->filter(fn (RatingAnswer $answer) => $answer->question?->target_type === $targetType
                && $answer->question?->answer_type === Question::TYPE_RATING
                && in_array((int) ($answer->answer_value[0] ?? 0), [1, 2, 3, 4, 5], true))
            ->groupBy('question_id')
            ->map(function (Collection $answers) {
                $question = $answers->first()->question;
                $average = round($answers->avg(fn (RatingAnswer $answer) => (int) $answer->answer_value[0]), 2);
                $weight = (int) $question->weight;

                return [
                    'question_id' => $question->id,
                    'question' => $question,
                    'average' => $average,
                    'percentage' => round(($average / 5) * 100, 2),
                    'weight' => $weight,
                    'contribution' => round(($average / 5) * $weight, 2),
                ];
            })
            ->sortBy(fn (array $row) => [$row['question']->sort_order, $row['question']->id])
            ->values();
    }

    private function reportIndicators(Collection $questions): Collection
    {
        return $questions
            ->groupBy('indicator_category_id')
            ->map(function (Collection $indicatorQuestions) {
                $firstQuestion = $indicatorQuestions->first();

                return [
                    'id' => $firstQuestion->indicator_category_id,
                    'name' => $firstQuestion->indicator ?: 'Indikator',
                    'questions' => $indicatorQuestions->values(),
                ];
            })
            ->values();
    }

    private function indicatorReportScores(Collection $ratings, string $targetType, Collection $indicators): Collection
    {
        $questionIds = $indicators->flatMap(fn (array $indicator) => $indicator['questions']->pluck('id'));
        $answers = $ratings->flatMap->answers
            ->filter(fn (RatingAnswer $answer) => $answer->question?->target_type === $targetType
                && $answer->question?->answer_type === Question::TYPE_RATING
                && $questionIds->contains($answer->question_id)
                && in_array((int) ($answer->answer_value[0] ?? 0), [1, 2, 3, 4, 5], true))
            ->groupBy(fn (RatingAnswer $answer) => $answer->question->indicator_category_id);

        return $indicators->mapWithKeys(function (array $indicator) use ($answers) {
            $indicatorAnswers = $answers->get($indicator['id'], collect());

            return [
                $indicator['id'] => $indicatorAnswers->isEmpty()
                    ? null
                    : round($indicatorAnswers->avg(fn (RatingAnswer $answer) => (int) $answer->answer_value[0]) * 2, 1),
            ];
        });
    }

    private function attendances(CarbonImmutable $period, Collection $driverIds): Collection
    {
        if ($driverIds->isEmpty()) {
            return collect();
        }

        return DriverAttendance::query()
            ->whereDate('period', $period->toDateString())
            ->whereIn('driver_id', $driverIds)
            ->get();
    }

    private function ratings(CarbonImmutable $period, Collection $driverIds): Collection
    {
        if ($driverIds->isEmpty()) {
            return collect();
        }

        return Rating::query()
            ->whereIn('driver_id', $driverIds)
            ->whereBetween('submitted_at', [
                $period->utc(),
                $period->endOfMonth()->endOfDay()->utc(),
            ])
            ->with(['vehicle', 'answers.question.indicatorCategory'])
            ->get();
    }

    private function vehicleRatings(CarbonImmutable $period, Collection $vehicleIds, ?int $driverId = null): Collection
    {
        if ($vehicleIds->isEmpty()) {
            return collect();
        }

        return Rating::query()
            ->whereIn('vehicle_id', $vehicleIds)
            ->when($driverId, fn (Builder $query) => $query->where('driver_id', $driverId))
            ->whereBetween('submitted_at', [
                $period->utc(),
                $period->endOfMonth()->endOfDay()->utc(),
            ])
            ->with(['driver.employeeCategory', 'vehicle', 'answers.question.indicatorCategory'])
            ->get();
    }

    private function eligibleDrivers($query)
    {
        return $query->active()->whereHas('employeeCategory', fn (Builder $category) => $category
            ->active()
            ->where('requires_sim', true));
    }
}
