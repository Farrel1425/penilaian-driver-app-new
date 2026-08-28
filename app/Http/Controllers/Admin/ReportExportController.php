<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RatingAnalyticsService;
use App\Support\Admin\RatingReportFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function excel(Request $request, string $type, RatingAnalyticsService $analytics): StreamedResponse
    {
        [$title, $rows] = $this->report($request, $type, $analytics);

        return response()->streamDownload(function () use ($title, $type, $rows): void {
            $headers = $type === 'branch'
                ? ['Unit Kerja', 'Driver', 'Kendaraan', 'Total Penilaian', 'Rating Rata-rata', 'Top Driver', 'Top Kendaraan']
                : [$type === 'driver' ? 'Driver' : 'Kendaraan', 'Unit Kerja', 'Total Penilaian', 'Rating Rata-rata'];

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekap');
            $sheet->mergeCells('A1:'.chr(64 + count($headers)).'1');
            $sheet->setCellValue('A1', $title);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('10284D');
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->fromArray($headers, null, 'A3');
            $lastHeader = chr(64 + count($headers)).'3';
            $sheet->getStyle('A3:'.$lastHeader)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A3:'.$lastHeader)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2859DF');
            $sheet->getStyle('A3:'.$lastHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $data = [];
            foreach ($rows as $row) {
                $data[] = $type === 'branch'
                    ? [$row['branch'], $row['drivers'], $row['vehicles'], $row['total'], $row['average'], $row['top_driver'], $row['top_vehicle']]
                    : [$row['name'], $row['branch'], $row['total'], $row['average']];
            }
            if ($data !== []) {
                $sheet->fromArray($data, null, 'A4');
            }
            foreach (range('A', chr(64 + count($headers))) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            $sheet->freezePane('A4');

            (new Xlsx($spreadsheet))->save('php://output');
        }, str($title)->slug().'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function pdf(Request $request, string $type, RatingAnalyticsService $analytics)
    {
        [$title, $rows] = $this->report($request, $type, $analytics);

        return Pdf::loadView('admin.reports.pdf', compact('title', 'rows', 'type'))
            ->setPaper('a4', 'landscape')
            ->download(str($title)->slug().'.pdf');
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
