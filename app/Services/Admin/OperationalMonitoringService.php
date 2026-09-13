<?php

namespace App\Services\Admin;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\DriverAttendance;
use App\Models\Question;
use App\Models\Rating;
use App\Models\RatingAnswer;
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

    public function branchRows(CarbonImmutable $period, ?int $branchId = null): Collection
    {
        $branches = Branch::query()
            ->active()
            ->when($branchId, fn (Builder $query) => $query->whereKey($branchId))
            ->with([
                'drivers' => fn ($query) => $this->eligibleDrivers($query)->orderBy('full_name'),
            ])
            ->withCount(['vehicles' => fn ($query) => $query->active()])
            ->orderBy('name')
            ->get();

        $driverIds = $branches->flatMap->drivers->pluck('id');
        $attendances = $this->attendances($period, $driverIds)->keyBy('driver_id');
        $ratings = $this->ratings($period, $driverIds)->groupBy('driver_id');

        return $branches->map(function (Branch $branch) use ($attendances, $ratings) {
            $driverRows = $branch->drivers->map(fn (Driver $driver) => $this->driverRow(
                $driver,
                $ratings->get($driver->id, collect()),
                $attendances->get($driver->id),
            ));
            $completed = $driverRows->where('is_complete', true)->count();
            $attendanceScores = $driverRows->pluck('attendance_score')->filter(fn ($score) => $score !== null);

            return [
                'branch' => $branch,
                'vehicles' => $branch->vehicles_count,
                'drivers' => $driverRows->count(),
                'completed' => $completed,
                'attendance_average' => $attendanceScores->isEmpty() ? null : round($attendanceScores->avg(), 2),
                'is_complete' => $driverRows->isNotEmpty() && $completed === $driverRows->count(),
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

        return $drivers->map(fn (Driver $driver) => $this->driverRow(
            $driver,
            $ratings->get($driver->id, collect()),
            $attendances->get($driver->id),
        ));
    }

    public function driverDetail(Branch $branch, Driver $driver, CarbonImmutable $period): array
    {
        $ratings = $this->ratings($period, collect([$driver->id]))->sortByDesc('submitted_at')->values();
        $attendance = $this->attendances($period, collect([$driver->id]))->first();
        $row = $this->driverRow($driver, $ratings, $attendance);
        $row['question_breakdown'] = $this->questionBreakdown($ratings, Question::TARGET_DRIVER);
        $row['vehicle_score'] = $this->weightedScore($ratings, Question::TARGET_VEHICLE);
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

        return [
            'branch' => $branch,
            'questions' => $questions,
            'rows' => $rows->map(function (array $row) use ($questions) {
                $breakdown = $this->questionBreakdown($row['ratings'], Question::TARGET_DRIVER)->keyBy('question_id');
                $row['report_scores'] = $questions->mapWithKeys(fn (Question $question) => [
                    $question->id => isset($breakdown[$question->id])
                        ? round($breakdown[$question->id]['percentage'] / 10, 1)
                        : null,
                ]);

                return $row;
            }),
        ];
    }

    private function driverRow(Driver $driver, Collection $ratings, ?DriverAttendance $attendance): array
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
            'final_score' => $driverScore !== null && $attendanceScore !== null
                ? round(($driverScore * 0.9) + ($attendanceScore * 0.1), 2)
                : null,
            'is_complete' => $attendanceScore !== null,
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

    private function eligibleDrivers($query)
    {
        return $query->active()->whereHas('employeeCategory', fn (Builder $category) => $category
            ->active()
            ->where('requires_sim', true));
    }
}
