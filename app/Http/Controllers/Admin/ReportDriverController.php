<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RatingAnalyticsService;
use App\Support\Admin\RatingReportFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportDriverController extends Controller
{
    public function __invoke(Request $request, RatingAnalyticsService $analytics): View
    {
        $filters = RatingReportFilters::fromRequest($request);
        $ratings = $analytics->history($filters);

        return view('admin.reports.drivers', [
            'filters' => $filters,
            'branches' => $analytics->branches(),
            'drivers' => $analytics->drivers($filters->branchId),
            'data' => $analytics->driverReport($filters),
            'questionScores' => $analytics->questionScores($filters, 'driver'),
            'comments' => $analytics->comments($ratings, 'driver')->take(6),
        ]);
    }
}
