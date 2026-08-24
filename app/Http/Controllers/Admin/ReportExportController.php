<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RatingAnalyticsService;
use App\Support\Admin\RatingReportFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function excel(Request $request, string $type, RatingAnalyticsService $analytics): StreamedResponse
    {
        [$title, $rows] = $this->report($request, $type, $analytics);

        return response()->streamDownload(function () use ($type, $rows): void {
            $output = fopen('php://output', 'w');
            $headers = $type === 'branch'
                ? ['Unit Kerja', 'Driver', 'Kendaraan', 'Total Penilaian', 'Rating Rata-rata', 'Top Driver', 'Top Kendaraan']
                : [$type === 'driver' ? 'Driver' : 'Kendaraan', 'Unit Kerja', 'Total Penilaian', 'Rating Rata-rata'];
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, $type === 'branch'
                    ? [$row['branch'], $row['drivers'], $row['vehicles'], $row['total'], $row['average'], $row['top_driver'], $row['top_vehicle']]
                    : [$row['name'], $row['branch'], $row['total'], $row['average']]);
            }
            fclose($output);
        }, str($title)->slug().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function print(Request $request, string $type, RatingAnalyticsService $analytics): View
    {
        [$title, $rows] = $this->report($request, $type, $analytics);

        return view('admin.reports.print', compact('title', 'rows', 'type'));
    }

    private function report(Request $request, string $type, RatingAnalyticsService $analytics): array
    {
        abort_unless(in_array($type, ['driver', 'vehicle', 'branch'], true), 404);
        $filters = RatingReportFilters::fromRequest($request);
        $data = $type === 'branch' ? $analytics->branchReport($filters) : $analytics->recap($filters, $type);

        return [
            match ($type) {
                'driver' => 'Report Driver',
                'vehicle' => 'Report Kendaraan',
                default => 'Report Unit Kerja',
            },
            $data['rows'],
        ];
    }
}
