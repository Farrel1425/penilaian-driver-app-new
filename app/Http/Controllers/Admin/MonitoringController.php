<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Services\Admin\RatingAnalyticsService;
use App\Support\Admin\RatingReportFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringController extends Controller
{
    public function index(Request $request, RatingAnalyticsService $analytics): View
    {
        $filters = RatingReportFilters::fromRequest($request);
        $ratings = $analytics->monitoring($filters)['ratings'];

        return view('admin.assessments.index', [
            'filters' => $filters,
            'branches' => $analytics->branches($filters->branchId),
            'drivers' => $analytics->drivers($filters->branchId),
            'vehicles' => $analytics->vehicles($filters->branchId),
            'ratings' => $ratings,
            'analytics' => $analytics,
        ]);
    }

    public function show(Rating $rating, RatingAnalyticsService $analytics): View
    {
        $rating->load(['branch', 'driver.branch', 'vehicle.branch', 'answers.question.options']);

        return view('admin.assessments.show', [
            'rating' => $rating,
            'analytics' => $analytics,
            'comments' => $analytics->comments(collect([$rating])),
        ]);
    }

    public function export(Request $request, RatingAnalyticsService $analytics): StreamedResponse
    {
        $ratings = $analytics->history(RatingReportFilters::fromRequest($request));

        return response()->streamDownload(function () use ($ratings, $analytics): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Tanggal & Jam', 'Unit Kerja', 'Kendaraan', 'Driver', 'Rating', 'Komentar']);
            foreach ($ratings as $rating) {
                fputcsv($output, [
                    $rating->submitted_at?->timezone(config('app.display_timezone'))?->format('Y-m-d H:i'),
                    $rating->branch?->name,
                    trim(($rating->vehicle?->police_number ?? '').' '.($rating->vehicle?->brand ?? '').' '.($rating->vehicle?->model ?? '')),
                    $rating->driver?->full_name,
                    $analytics->ratingScore($rating),
                    $analytics->comments(collect([$rating]))->pluck('text')->implode(' | '),
                ]);
            }
            fclose($output);
        }, 'riwayat-penilaian.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function recap(Request $request, RatingAnalyticsService $analytics): View
    {
        $filters = RatingReportFilters::fromRequest($request);
        $group = $request->string('group')->value();
        $group = in_array($group, ['driver', 'vehicle', 'branch'], true) ? $group : 'driver';

        return view('admin.assessments.recap', [
            'filters' => $filters,
            'branches' => $analytics->branches($filters->branchId),
            'drivers' => $analytics->drivers($filters->branchId),
            'vehicles' => $analytics->vehicles($filters->branchId),
            'group' => $group,
            'data' => $analytics->recap($filters, $group),
        ]);
    }
}
