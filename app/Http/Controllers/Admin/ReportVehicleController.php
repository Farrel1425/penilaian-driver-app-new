<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RatingAnalyticsService;
use App\Support\Admin\RatingReportFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportVehicleController extends Controller
{
    public function __invoke(Request $request, RatingAnalyticsService $analytics): View
    {
        $filters = RatingReportFilters::fromRequest($request);
        $ratings = $analytics->history($filters);

        return view('admin.reports.vehicles', [
            'filters' => $filters,
            'branches' => $analytics->branches(),
            'vehicles' => $analytics->vehicles($filters->branchId),
            'data' => $analytics->vehicleReport($filters),
            'questionScores' => $analytics->questionScores($filters, 'vehicle'),
            'comments' => $analytics->comments($ratings, 'vehicle')->take(6),
        ]);
    }
}
