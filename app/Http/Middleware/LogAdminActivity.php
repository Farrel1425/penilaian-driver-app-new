<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\IndicatorCategory;
use App\Models\Question;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() < 400 && $request->user() instanceof User && ($entry = $this->entry($request))) {
            $this->logger->log($request->user(), $entry[0], $entry[1], $entry[2], $request, [
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
            ]);
        }

        return $response;
    }

    /** @return array{string, string, string}|null */
    private function entry(Request $request): ?array
    {
        $route = (string) $request->route()?->getName();
        $module = match (true) {
            str_starts_with($route, 'admin.branches.') => 'Unit Kerja',
            str_starts_with($route, 'admin.employees.') => 'Pegawai',
            str_starts_with($route, 'admin.employee-categories.') => 'Kategori Pegawai',
            str_starts_with($route, 'admin.indicator-categories.') => 'Kategori Indikator',
            str_starts_with($route, 'admin.vehicles.') => 'Kendaraan',
            str_starts_with($route, 'admin.questions.') => 'Pertanyaan',
            str_starts_with($route, 'admin.users.') => 'Pengguna',
            str_starts_with($route, 'admin.assessments.') => 'Penilaian',
            str_starts_with($route, 'admin.reports.') => 'Laporan',
            str_starts_with($route, 'admin.settings.') => 'Pengaturan Sistem',
            str_starts_with($route, 'admin.activity-logs.') => 'Log Aktivitas',
            default => null,
        };

        if (! $module) {
            return null;
        }

        $action = match (true) {
            str_ends_with($route, '.store') => 'Tambah',
            str_ends_with($route, '.update') => 'Edit',
            str_ends_with($route, '.destroy') => 'Hapus',
            str_contains($route, 'toggle-status') => 'Ubah Status',
            str_contains($route, 'regenerate-qr') => 'Regenerate QR',
            str_contains($route, '.qr.download') => 'Unduh QR',
            str_ends_with($route, '.export') => 'Export Excel',
            str_ends_with($route, '.print') => 'Cetak Laporan',
            default => null,
        };

        if (! $action) {
            return null;
        }

        return [$module, $action, sprintf('%s: %s%s.', $action, $module, $this->subject($request))];
    }

    private function subject(Request $request): string
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            $label = match (true) {
                $parameter instanceof Branch => $parameter->name,
                $parameter instanceof Driver => $parameter->full_name,
                $parameter instanceof IndicatorCategory => $parameter->name,
                $parameter instanceof Vehicle => $parameter->police_number,
                $parameter instanceof Question => $parameter->question,
                $parameter instanceof User => $parameter->name,
                default => null,
            };

            if ($label) {
                return ' - '.str($label)->limit(80);
            }
        }

        return '';
    }
}
