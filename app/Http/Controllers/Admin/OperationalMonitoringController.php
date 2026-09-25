<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\DriverAttendance;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Admin\OperationalMonitoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OperationalMonitoringController extends Controller
{
    public function index(Request $request, OperationalMonitoringService $monitoring): View
    {
        $period = $monitoring->period($request->string('period')->value());
        $rows = $monitoring->branchRows($period, $this->scopedBranchId($request));
        $target = $this->target($request);
        $search = $request->string('search')->trim()->lower()->value();
        $status = $request->string('status')->value();

        $rows = $rows
            ->when($search, fn (Collection $items) => $items->filter(fn (array $row) => str($row['branch']->code.' '.$row['branch']->name.' '.$row['branch']->regency)->lower()->contains($search)))
            ->when(in_array($status, ['complete', 'incomplete'], true), fn (Collection $items) => $items->filter(
                fn (array $row) => $row[$target === 'vehicle' ? 'is_vehicle_complete' : 'is_complete'] === ($status === 'complete')
            ))
            ->values();

        return view('admin.monitoring.index', [
            'period' => $period,
            'rows' => $this->paginate($rows, $request, 9),
            'status' => $status,
            'target' => $target,
        ]);
    }

    public function show(Request $request, Branch $branch, OperationalMonitoringService $monitoring): View
    {
        $this->authorizeBranch($request, $branch);
        $period = $monitoring->period($request->string('period')->value());
        $target = $this->target($request);
        $rows = $target === 'vehicle'
            ? $monitoring->vehicleRows($branch, $period)
            : $monitoring->driverRows($branch, $period);
        $status = $request->string('status')->value();

        $rows = $rows
            ->when(in_array($status, ['complete', 'incomplete'], true), fn (Collection $items) => $items->filter(
                fn (array $row) => $row[$target === 'vehicle' ? 'has_ratings' : 'is_complete'] === ($status === 'complete')
            ))
            ->values();

        return view('admin.monitoring.show', [
            'branch' => $branch,
            'period' => $period,
            'rows' => $this->paginate($rows, $request, 10),
            'status' => $status,
            'workingDays' => $monitoring->workingDays($period),
            'target' => $target,
        ]);
    }

    public function driver(Request $request, Branch $branch, Driver $driver, OperationalMonitoringService $monitoring): View
    {
        $this->authorizeDriver($request, $branch, $driver);
        $period = $monitoring->period($request->string('period')->value());

        return view('admin.monitoring.driver', [
            'period' => $period,
            ...$monitoring->driverDetail($branch, $driver, $period),
        ]);
    }

    public function vehicle(Request $request, Branch $branch, Vehicle $vehicle, OperationalMonitoringService $monitoring): View
    {
        $this->authorizeVehicle($request, $branch, $vehicle);
        $period = $monitoring->period($request->string('period')->value());

        return view('admin.monitoring.vehicle', [
            'period' => $period,
            ...$monitoring->vehicleDetail($branch, $vehicle, $period),
        ]);
    }

    public function exportDriver(Request $request, Branch $branch, Driver $driver, OperationalMonitoringService $monitoring)
    {
        $this->authorizeDriver($request, $branch, $driver);
        $period = $monitoring->period($request->string('period')->value());
        $report = $monitoring->driverReport($branch, $driver, $period);
        $filename = str('monitoring-driver-'.$driver->full_name.'-'.$period->format('Y-m'))->slug().'.pdf';

        return Pdf::loadView('admin.monitoring.individual-pdf', compact('report'))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function exportVehicle(Request $request, Branch $branch, Vehicle $vehicle, OperationalMonitoringService $monitoring)
    {
        $this->authorizeVehicle($request, $branch, $vehicle);
        $period = $monitoring->period($request->string('period')->value());
        $report = $monitoring->vehicleReport($branch, $vehicle, $period);
        $filename = str('monitoring-kendaraan-'.$vehicle->police_number.'-'.$period->format('Y-m'))->slug().'.pdf';

        return Pdf::loadView('admin.monitoring.individual-pdf', compact('report'))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function storeAttendance(Request $request, Branch $branch, Driver $driver, OperationalMonitoringService $monitoring): RedirectResponse
    {
        $this->authorizeDriver($request, $branch, $driver);
        $data = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'present_days' => ['required', 'integer', 'min:0'],
            'sick_days' => ['required', 'integer', 'min:0'],
            'permitted_days' => ['required', 'integer', 'min:0'],
            'absent_days' => ['required', 'integer', 'min:0'],
        ]);

        $period = $monitoring->period($data['period']);
        $workingDays = $monitoring->workingDays($period);
        $total = collect($data)->only(['present_days', 'sick_days', 'permitted_days', 'absent_days'])->sum();
        if ($total < 1) {
            throw ValidationException::withMessages(['present_days' => 'Total data absensi harus lebih dari 0 hari.']);
        }
        if ($total > $workingDays) {
            throw ValidationException::withMessages(['present_days' => "Total data absensi tidak boleh lebih dari {$workingDays} hari kerja pada periode ini."]);
        }

        $attendance = DriverAttendance::query()
            ->where('driver_id', $driver->id)
            ->whereDate('period', $period->toDateString())
            ->first() ?? new DriverAttendance;
        $attendance->fill([
            'branch_id' => $branch->id,
            'driver_id' => $driver->id,
            'entered_by' => $request->user()->id,
            'period' => $period->toDateString(),
            'present_days' => $data['present_days'],
            'sick_days' => $data['sick_days'],
            'permitted_days' => $data['permitted_days'],
            'absent_days' => $data['absent_days'],
        ])->save();

        return back()->with('success', 'Data absensi '.$driver->full_name.' berhasil disimpan.');
    }

    public function report(Request $request, OperationalMonitoringService $monitoring, ?Branch $branch = null): View
    {
        if ($branch) {
            $this->authorizeBranch($request, $branch);
        }
        $period = $monitoring->period($request->string('period')->value());
        $branches = $branch
            ? collect([$branch])
            : Branch::query()->active()->when($this->scopedBranchId($request), fn ($query, $id) => $query->whereKey($id))->orderBy('name')->get();

        return view('admin.monitoring.report', [
            'period' => $period,
            'reports' => $branches->map(fn (Branch $item) => $monitoring->reportRows($item, $period)),
            'branch' => $branch,
        ]);
    }

    public function export(Request $request, OperationalMonitoringService $monitoring, ?Branch $branch = null)
    {
        if ($branch) {
            $this->authorizeBranch($request, $branch);
        }
        $period = $monitoring->period($request->string('period')->value());
        $branches = $branch
            ? collect([$branch])
            : Branch::query()->active()->when($this->scopedBranchId($request), fn ($query, $id) => $query->whereKey($id))->orderBy('name')->get();
        $reports = $branches->map(fn (Branch $item) => $monitoring->reportRows($item, $period));
        $filename = str('monitoring-'.($branch?->code ?? 'semua-cabang').'-'.$period->format('Y-m'))->slug().'.pdf';

        return Pdf::loadView('admin.monitoring.pdf', compact('period', 'reports'))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    private function scopedBranchId(Request $request): ?int
    {
        return $request->user()?->role === User::ROLE_BRANCH_ADMIN ? $request->user()->branch_id : null;
    }

    private function authorizeBranch(Request $request, Branch $branch): void
    {
        $scopedBranchId = $this->scopedBranchId($request);
        abort_if($scopedBranchId && $branch->id !== $scopedBranchId, 403);
    }

    private function authorizeDriver(Request $request, Branch $branch, Driver $driver): void
    {
        $this->authorizeBranch($request, $branch);
        abort_unless($driver->branch_id === $branch->id && $driver->newQuery()->whereKey($driver->getKey())->eligibleForAssessment()->exists(), 404);
    }

    private function authorizeVehicle(Request $request, Branch $branch, Vehicle $vehicle): void
    {
        $this->authorizeBranch($request, $branch);
        abort_unless(
            $vehicle->branch_id === $branch->id
                && $vehicle->newQuery()->whereKey($vehicle->getKey())->active()->exists(),
            404,
        );
    }

    private function target(Request $request): string
    {
        return $request->string('target')->value() === 'vehicle' ? 'vehicle' : 'driver';
    }

    private function paginate(Collection $items, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );
    }
}
