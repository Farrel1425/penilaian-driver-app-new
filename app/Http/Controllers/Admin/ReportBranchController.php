<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RatingAnalyticsService;
use App\Support\Admin\RatingReportFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportBranchController extends Controller
{
    public function __invoke(Request $request, RatingAnalyticsService $analytics): View
    {
        $filters = RatingReportFilters::fromRequest($request);

        return view('admin.reports.branches', [
            'filters' => $filters,
            'branches' => $analytics->branches($filters->branchId),
            'data' => $analytics->branchReport($filters),
        ]);
    }
}
