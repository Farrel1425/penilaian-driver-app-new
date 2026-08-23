<?php

namespace App\Services\Admin;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\Question;
use App\Models\Rating;
use App\Models\RatingAnswer;
use App\Models\Vehicle;
use App\Support\Admin\RatingReportFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RatingAnalyticsService
{
    public function dashboard(RatingReportFilters $filters): array
    {
        $ratings = $this->ratings($filters)->with(['branch', 'driver', 'vehicle'])->latest('submitted_at')->get();
        $ratingAnswers = $this->ratingAnswers($filters)->with(['rating.driver.branch', 'rating.vehicle.branch', 'rating.branch', 'question'])->get();
        $driverRatingAnswers = $ratingAnswers->where('question.target_type', Question::TARGET_DRIVER);
        $vehicleRatingAnswers = $ratingAnswers->where('question.target_type', Question::TARGET_VEHICLE);

        return [
            'stats' => [
                'total_assessments' => $ratings->count(),
                'average_driver_rating' => $this->average($driverRatingAnswers),
                'average_vehicle_rating' => $this->average($vehicleRatingAnswers),
                'today_assessments' => $ratings->filter(fn ($rating) => $rating->submitted_at?->isToday())->count(),
            ],
            'trend' => $this->trend($ratingAnswers),
            'distribution' => $this->distribution($ratingAnswers),
            'latestRatings' => $ratings->take(8),
            'branchStats' => $this->branchStats($ratings, $ratingAnswers),
            'driverRanking' => $this->driverRanking($ratingAnswers)->take(5),
            'vehicleRanking' => $this->vehicleRanking($ratingAnswers)->take(5),
        ];
    }

    public function monitoring(RatingReportFilters $filters): array
    {
        $ratings = $this->ratings($filters)->with(['branch', 'driver', 'vehicle', 'answers.question'])->latest('submitted_at')->paginate(12)->withQueryString();

        return ['ratings' => $ratings];
    }

    public function driverReport(RatingReportFilters $filters): array
    {
        $answers = $this->ratingAnswers($filters, Question::TARGET_DRIVER)->with(['rating.driver.branch', 'question'])->get();

        return [
            'stats' => [
                'total_driver' => Driver::query()->when($filters->branchId, fn ($q) => $q->where('branch_id', $filters->branchId))->count(),
                'average_rating' => $this->average($answers),
                'total_assessments' => $answers->pluck('rating_id')->unique()->count(),
            ],
            'distribution' => $this->distribution($answers),
            'trend' => $this->trend($answers),
            'performance' => $this->driverRanking($answers),
        ];
    }

    public function vehicleReport(RatingReportFilters $filters): array
    {
        $answers = $this->ratingAnswers($filters, Question::TARGET_VEHICLE)->with(['rating.vehicle.branch', 'question'])->get();

        return [
            'stats' => [
                'total_vehicle' => Vehicle::query()->when($filters->branchId, fn ($q) => $q->where('branch_id', $filters->branchId))->count(),
                'average_rating' => $this->average($answers),
                'total_assessments' => $answers->pluck('rating_id')->unique()->count(),
            ],
            'distribution' => $this->distribution($answers),
            'trend' => $this->trend($answers),
            'performance' => $this->vehicleRanking($answers),
        ];
    }

    public function branches(): Collection
    {
        return Branch::query()->orderBy('name')->get();
    }

    public function ratingScore(Rating $rating, ?string $targetType = null): ?float
    {
        $answers = $rating->answers->filter(fn ($answer) => $answer->question?->answer_type === Question::TYPE_RATING)
            ->when($targetType, fn ($items) => $items->filter(fn ($answer) => $answer->question?->target_type === $targetType));

        return $this->average($answers);
    }

    private function ratings(RatingReportFilters $filters): Builder
    {
        return Rating::query()
            ->when($filters->branchId, fn ($q) => $q->where('branch_id', $filters->branchId))
            ->when($filters->startDate, fn ($q) => $q->where('submitted_at', '>=', $filters->startDate))
            ->when($filters->endDate, fn ($q) => $q->where('submitted_at', '<=', $filters->endDate));
    }

    private function ratingAnswers(RatingReportFilters $filters, ?string $targetType = null): Builder
    {
        return RatingAnswer::query()
            ->whereHas('question', fn ($q) => $q->where('answer_type', Question::TYPE_RATING)->when($targetType, fn ($q) => $q->where('target_type', $targetType)))
            ->whereHas('rating', fn ($q) => $q
                ->when($filters->branchId, fn ($q) => $q->where('branch_id', $filters->branchId))
                ->when($filters->startDate, fn ($q) => $q->where('submitted_at', '>=', $filters->startDate))
                ->when($filters->endDate, fn ($q) => $q->where('submitted_at', '<=', $filters->endDate))
            );
    }

    private function value(RatingAnswer $answer): ?int
    {
        $value = $answer->answer_value[0] ?? null;

        return in_array((int) $value, [1, 2, 3, 4, 5], true) ? (int) $value : null;
    }

    private function average(Collection $answers): ?float
    {
        $values = $answers->map(fn ($answer) => $this->value($answer))->filter(fn ($value) => $value !== null);

        return $values->isEmpty() ? null : round($values->avg(), 2);
    }

    private function distribution(Collection $answers): array
    {
        $base = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($answers as $answer) {
            $value = $this->value($answer);
            if ($value !== null) {
                $base[$value]++;
            }
        }

        return $base;
    }

    private function trend(Collection $answers): Collection
    {
        return $answers
            ->filter(fn ($answer) => $answer->rating?->submitted_at && $this->value($answer) !== null)
            ->groupBy(fn ($answer) => $answer->rating->submitted_at->toDateString())
            ->map(fn ($items, $date) => ['date' => $date, 'average' => $this->average($items), 'count' => $items->pluck('rating_id')->unique()->count()])
            ->sortBy('date')
            ->values();
    }

    private function branchStats(Collection $ratings, Collection $answers): Collection
    {
        return $ratings->groupBy('branch_id')->map(function ($items, $branchId) use ($answers) {
            $branchAnswers = $answers->filter(fn ($answer) => $answer->rating?->branch_id === (int) $branchId);

            return ['branch' => $items->first()->branch?->name ?? '-', 'total' => $items->count(), 'average' => $this->average($branchAnswers)];
        })->sortByDesc('total')->values();
    }

    private function driverRanking(Collection $answers): Collection
    {
        return $answers->filter(fn ($answer) => $answer->rating?->driver)->groupBy(fn ($answer) => $answer->rating->driver_id)->map(function ($items) {
            $driver = $items->first()->rating->driver;

            return ['name' => $driver->full_name, 'branch' => $driver->branch?->name, 'total' => $items->pluck('rating_id')->unique()->count(), 'average' => $this->average($items), 'distribution' => $this->distribution($items)];
        })->sortByDesc(fn ($row) => [$row['average'] ?? 0, $row['total']])->values();
    }

    private function vehicleRanking(Collection $answers): Collection
    {
        return $answers->filter(fn ($answer) => $answer->rating?->vehicle)->groupBy(fn ($answer) => $answer->rating->vehicle_id)->map(function ($items) {
            $vehicle = $items->first()->rating->vehicle;

            return ['name' => $vehicle->police_number, 'branch' => $vehicle->branch?->name, 'label' => trim($vehicle->brand.' '.$vehicle->model), 'total' => $items->pluck('rating_id')->unique()->count(), 'average' => $this->average($items), 'distribution' => $this->distribution($items)];
        })->sortByDesc(fn ($row) => [$row['average'] ?? 0, $row['total']])->values();
    }
}
