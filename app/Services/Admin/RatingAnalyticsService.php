<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
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
        $ratings = $this->ratings($filters)->with(['branch', 'driver.branch', 'vehicle.branch', 'answers.question'])->latest('submitted_at')->get();
        $ratingAnswers = $this->ratingAnswers($filters)->with(['rating.driver.branch', 'rating.vehicle.branch', 'rating.branch', 'question'])->get();
        $driverRatingAnswers = $ratingAnswers->where('question.target_type', Question::TARGET_DRIVER);
        $vehicleRatingAnswers = $ratingAnswers->where('question.target_type', Question::TARGET_VEHICLE);

        return [
            'stats' => [
                'total_assessments' => $ratings->count(),
                'average_driver_rating' => $this->average($driverRatingAnswers),
                'average_vehicle_rating' => $this->average($vehicleRatingAnswers),
                'rated_drivers' => $ratings->pluck('driver_id')->filter()->unique()->count(),
                'rated_vehicles' => $ratings->pluck('vehicle_id')->filter()->unique()->count(),
                'today_assessments' => $ratings->filter(fn ($rating) => $rating->submitted_at?->isToday())->count(),
            ],
            'trend' => $this->dashboardTrend($driverRatingAnswers, $vehicleRatingAnswers),
            'driverDistribution' => $this->distribution($driverRatingAnswers),
            'vehicleDistribution' => $this->distribution($vehicleRatingAnswers),
            'latestRatings' => $ratings->take(8),
            'branchStats' => $this->branchStats($ratings, $ratingAnswers),
            'driverRanking' => $this->driverRanking($ratingAnswers)->take(5),
            'latestActivities' => $this->latestActivities($ratings),
        ];
    }

    public function monitoring(RatingReportFilters $filters): array
    {
        $ratings = $this->ratings($filters)->with(['branch', 'driver', 'vehicle', 'answers.question'])->latest('submitted_at')->paginate(12)->withQueryString();

        return ['ratings' => $ratings];
    }

    public function drivers(?int $branchId = null): Collection
    {
        return Driver::query()->with('branch')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->orderBy('full_name')->get();
    }

    public function vehicles(?int $branchId = null): Collection
    {
        return Vehicle::query()->with('branch')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->orderBy('police_number')->get();
    }

    public function history(RatingReportFilters $filters): Collection
    {
        return $this->ratings($filters)
            ->with(['branch', 'driver.branch', 'vehicle.branch', 'answers.question'])
            ->latest('submitted_at')
            ->get();
    }

    public function recap(RatingReportFilters $filters, string $group = 'driver'): array
    {
        $ratings = $this->history($filters);
        $answers = $ratings->flatMap->answers;

        $rows = match ($group) {
            'vehicle' => $this->vehicleRanking($answers),
            'branch' => $this->branchStats($ratings, $answers),
            default => $this->driverRanking($answers),
        };

        return [
            'stats' => [
                'total_assessments' => $ratings->count(),
                'average_rating' => $this->average($answers),
                'total_entities' => $rows->count(),
            ],
            'trend' => $this->trend($answers),
            'distribution' => $this->distribution($answers),
            'rows' => $rows,
        ];
    }

    public function branchReport(RatingReportFilters $filters): array
    {
        $ratings = $this->history($filters);
        $answers = $ratings->flatMap->answers;
        $rows = $this->branchStats($ratings, $answers);

        return [
            'stats' => [
                'total_assessments' => $ratings->count(),
                'average_rating' => $this->average($answers),
                'total_entities' => $rows->count(),
                'total_branches' => $rows->count(),
                'top_branch' => $rows->first()['branch'] ?? '-',
            ],
            'distribution' => $this->distribution($answers),
            'rows' => $rows,
        ];
    }

    public function comments(Collection $ratings, ?string $targetType = null): Collection
    {
        return $ratings->flatMap(function (Rating $rating) use ($targetType) {
            return $rating->answers
                ->filter(fn (RatingAnswer $answer) => filled($answer->answer_text)
                    && in_array($answer->question?->answer_type, [Question::TYPE_SHORT_TEXT, Question::TYPE_PARAGRAPH], true)
                    && (! $targetType || $answer->question?->target_type === $targetType))
                ->map(fn (RatingAnswer $answer) => [
                    'text' => $answer->answer_text,
                    'question' => $answer->question?->question,
                    'rating' => $rating,
                ]);
        })->sortByDesc(fn (array $comment) => $comment['rating']->submitted_at)->values();
    }

    public function questionScores(RatingReportFilters $filters, string $targetType): Collection
    {
        return $this->ratingAnswers($filters, $targetType)->with('question')->get()
            ->groupBy('question_id')
            ->map(function (Collection $answers) {
                $question = $answers->first()->question;

                return [
                    'question' => $question?->question ?? '-',
                    'indicator' => $question?->indicator ?? '-',
                    'sort_order' => $question?->sort_order ?? PHP_INT_MAX,
                    'average' => $this->average($answers),
                    'total' => $answers->pluck('rating_id')->unique()->count(),
                ];
            })->sortBy('sort_order')->values();
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
            ->when($filters->driverId, fn ($q) => $q->where('driver_id', $filters->driverId))
            ->when($filters->vehicleId, fn ($q) => $q->where('vehicle_id', $filters->vehicleId))
            ->when($filters->startDate, fn ($q) => $q->where('submitted_at', '>=', $filters->startDate))
            ->when($filters->endDate, fn ($q) => $q->where('submitted_at', '<=', $filters->endDate))
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->whereHas('driver', fn ($driver) => $driver->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('vehicle', fn ($vehicle) => $vehicle->where('police_number', 'like', "%{$search}%")->orWhere('brand', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%"))
                        ->orWhereHas('branch', fn ($branch) => $branch->where('name', 'like', "%{$search}%"));
                });
            });
    }

    private function ratingAnswers(RatingReportFilters $filters, ?string $targetType = null): Builder
    {
        return RatingAnswer::query()
            ->whereHas('question', fn ($q) => $q->where('answer_type', Question::TYPE_RATING)->when($targetType, fn ($q) => $q->where('target_type', $targetType)))
            ->whereHas('rating', fn ($q) => $q
                ->when($filters->branchId, fn ($q) => $q->where('branch_id', $filters->branchId))
                ->when($filters->driverId, fn ($q) => $q->where('driver_id', $filters->driverId))
                ->when($filters->vehicleId, fn ($q) => $q->where('vehicle_id', $filters->vehicleId))
                ->when($filters->startDate, fn ($q) => $q->where('submitted_at', '>=', $filters->startDate))
                ->when($filters->endDate, fn ($q) => $q->where('submitted_at', '<=', $filters->endDate))
                ->when($filters->search, function ($query, string $search): void {
                    $query->where(function ($nested) use ($search): void {
                        $nested->whereHas('driver', fn ($driver) => $driver->where('full_name', 'like', "%{$search}%"))
                            ->orWhereHas('vehicle', fn ($vehicle) => $vehicle->where('police_number', 'like', "%{$search}%")->orWhere('brand', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%"))
                            ->orWhereHas('branch', fn ($branch) => $branch->where('name', 'like', "%{$search}%"));
                    });
                })
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

    private function dashboardTrend(Collection $driverAnswers, Collection $vehicleAnswers): Collection
    {
        $driverTrend = $this->trend($driverAnswers)->keyBy('date');
        $vehicleTrend = $this->trend($vehicleAnswers)->keyBy('date');

        return $driverTrend->keys()->merge($vehicleTrend->keys())->unique()->sort()->map(fn ($date) => [
            'date' => $date,
            'driver' => $driverTrend->get($date)['average'] ?? null,
            'vehicle' => $vehicleTrend->get($date)['average'] ?? null,
        ])->values();
    }

    private function branchStats(Collection $ratings, Collection $answers): Collection
    {
        $branches = Branch::query()->withCount(['drivers', 'vehicles'])->whereIn('id', $ratings->pluck('branch_id')->unique())->get()->keyBy('id');

        return $ratings->groupBy('branch_id')->map(function ($items, $branchId) use ($answers, $branches) {
            $branchAnswers = $answers->filter(fn ($answer) => $answer->rating?->branch_id === (int) $branchId);
            $branch = $branches->get($branchId);
            $drivers = $this->driverRanking($branchAnswers);
            $vehicles = $this->vehicleRanking($branchAnswers);

            return [
                'id' => (int) $branchId,
                'branch' => $items->first()->branch?->name ?? '-',
                'total' => $items->count(),
                'average' => $this->average($branchAnswers),
                'driver_average' => $this->average($branchAnswers->where('question.target_type', Question::TARGET_DRIVER)),
                'vehicle_average' => $this->average($branchAnswers->where('question.target_type', Question::TARGET_VEHICLE)),
                'drivers' => $branch?->drivers_count ?? 0,
                'vehicles' => $branch?->vehicles_count ?? 0,
                'top_driver' => $drivers->first()['name'] ?? '-',
                'top_vehicle' => $vehicles->first()['name'] ?? '-',
            ];
        })->sortByDesc(fn ($row) => [$row['average'] ?? 0, $row['total']])->values();
    }

    private function driverRanking(Collection $answers): Collection
    {
        return $answers->filter(fn ($answer) => $answer->rating?->driver)->groupBy(fn ($answer) => $answer->rating->driver_id)->map(function ($items) {
            $driver = $items->first()->rating->driver;

            return ['name' => $driver->full_name, 'photo' => $driver->photo, 'branch' => $driver->branch?->name, 'total' => $items->pluck('rating_id')->unique()->count(), 'average' => $this->average($items), 'distribution' => $this->distribution($items)];
        })->sortByDesc(fn ($row) => [$row['average'] ?? 0, $row['total']])->values();
    }

    private function vehicleRanking(Collection $answers): Collection
    {
        return $answers->filter(fn ($answer) => $answer->rating?->vehicle)->groupBy(fn ($answer) => $answer->rating->vehicle_id)->map(function ($items) {
            $vehicle = $items->first()->rating->vehicle;

            return ['name' => $vehicle->police_number, 'branch' => $vehicle->branch?->name, 'label' => trim($vehicle->brand.' '.$vehicle->model), 'total' => $items->pluck('rating_id')->unique()->count(), 'average' => $this->average($items), 'distribution' => $this->distribution($items)];
        })->sortByDesc(fn ($row) => [$row['average'] ?? 0, $row['total']])->values();
    }

    private function latestActivities(Collection $ratings): Collection
    {
        $ratingActivities = $ratings->take(6)->map(fn (Rating $rating) => [
            'type' => 'rating',
            'description' => sprintf('Penilaian baru untuk %s dan %s.', $rating->driver?->full_name ?? 'driver', $rating->vehicle?->police_number ?? 'kendaraan'),
            'created_at' => $rating->submitted_at,
        ]);

        $adminActivities = ActivityLog::query()->latest('created_at')->take(6)->get()->map(fn (ActivityLog $log) => [
            'type' => 'admin',
            'description' => $log->description,
            'created_at' => $log->created_at,
        ]);

        return $ratingActivities->merge($adminActivities)->sortByDesc('created_at')->take(6)->values();
    }
}
