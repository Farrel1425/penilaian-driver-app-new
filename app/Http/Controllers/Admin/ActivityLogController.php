<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.activity-logs.index', [
            'logs' => $this->filtered($request)->paginate(20)->withQueryString(),
            'admins' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request): Response
    {
        $filename = 'log-aktivitas-'.now()->format('Ymd-His').'.csv';
        $logs = $this->filtered($request)->with('user:id,name')->get();

        return response()->streamDownload(function () use ($logs): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal & Jam', 'Admin', 'Modul', 'Aktivitas', 'Keterangan', 'IP']);

            foreach ($logs as $log) {
                fputcsv($output, [
                    $log->created_at?->format('d/m/Y H:i:s'),
                    $log->user?->name ?? 'Sistem',
                    $log->module,
                    $log->action,
                    $log->description,
                    $log->ip_address,
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request)
    {
        return ActivityLog::query()
            ->with('user:id,name')
            ->when($request->filled('start_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('start_date')))
            ->when($request->filled('end_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('end_date')))
            ->when($request->integer('user_id'), fn ($query, int $id) => $query->where('user_id', $id))
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('module', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('created_at');
    }
}
