<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DriverRequest;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\EmployeeCategory;
use App\Services\PublicImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DriverController extends Controller
{
    public function index(Request $request): View
    {
        $drivers = Driver::query()
            ->with(['branch', 'employeeCategory'])
            ->withCount('ratings')
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('sim_number', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('branch_id'), fn ($query, int $branchId) => $query->where('branch_id', $branchId))
            ->when($request->integer('employee_category_id'), fn ($query, int $categoryId) => $query->where('employee_category_id', $categoryId))
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->dataCompleteness($request->string('completeness')->toString())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.drivers.index', [
            'drivers' => $drivers,
            'branches' => Branch::query()->orderBy('name')->get(),
            'categories' => EmployeeCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.drivers.create', [
            'driver' => new Driver,
            'branches' => Branch::query()->orderBy('name')->get(),
            'categories' => EmployeeCategory::active()->orderBy('name')->get(),
        ]);
    }

    public function store(DriverRequest $request, PublicImageStorage $images): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'sim_photo', 'remove_photo', 'remove_sim_photo']);
        $data['employee_category_id'] ??= EmployeeCategory::query()
            ->where('name', 'Driver')
            ->value('id');
        $data['photo'] = null;
        $data['sim_photo'] = null;
        try {
            $data['photo'] = $request->hasFile('photo') ? $images->store($request->file('photo'), 'drivers') : null;
            $data['sim_photo'] = $request->hasFile('sim_photo') ? $images->store($request->file('sim_photo'), 'driver-sims') : null;
            $driver = Driver::query()->create($data);
        } catch (Throwable $exception) {
            $images->deleteMany([$data['photo'], $data['sim_photo']]);
            throw $exception;
        }

        return redirect()->route('admin.employees.show', $driver)->with('status', 'Pegawai berhasil dibuat.');
    }

    public function show(Driver $driver): View
    {
        $driver->load(['branch', 'employeeCategory'])->loadCount('ratings');

        return view('admin.drivers.show', compact('driver'));
    }

    public function edit(Request $request, Driver $driver): View
    {
        return view('admin.drivers.edit', [
            'driver' => $driver,
            'branches' => Branch::query()->orderBy('name')->get(),
            'categories' => EmployeeCategory::query()->orderBy('name')->get(),
            'returnTo' => $request->string('return_to')->toString() === 'detail' ? 'detail' : 'index',
        ]);
    }

    public function update(DriverRequest $request, Driver $driver, PublicImageStorage $images): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'sim_photo', 'remove_photo', 'remove_sim_photo']);
        $data['employee_category_id'] ??= $driver->employee_category_id;
        $newFiles = [];
        $oldFiles = [];

        try {
            if ($request->hasFile('photo')) {
                $oldFiles[] = $driver->photo;
                $newFiles[] = $data['photo'] = $images->store($request->file('photo'), 'drivers');
            } elseif ($request->boolean('remove_photo')) {
                $oldFiles[] = $driver->photo;
                $data['photo'] = null;
            }

            if ($request->hasFile('sim_photo')) {
                $oldFiles[] = $driver->sim_photo;
                $newFiles[] = $data['sim_photo'] = $images->store($request->file('sim_photo'), 'driver-sims');
            } elseif ($request->boolean('remove_sim_photo')) {
                $oldFiles[] = $driver->sim_photo;
                $data['sim_photo'] = null;
            }

            $driver->update($data);
        } catch (Throwable $exception) {
            $images->deleteMany($newFiles);
            throw $exception;
        }
        $images->deleteMany($oldFiles);

        if ($request->input('return_to') === 'detail') {
            return redirect()->route('admin.employees.show', $driver)->with('status', 'Pegawai berhasil diperbarui.');
        }

        return redirect()->route('admin.employees.index')->with('status', 'Pegawai berhasil diperbarui.');
    }

    public function toggleStatus(Driver $driver): RedirectResponse
    {
        $driver->update(['status' => $driver->status === Driver::STATUS_ACTIVE ? Driver::STATUS_INACTIVE : Driver::STATUS_ACTIVE]);

        return back()->with('status', 'Status pegawai berhasil diperbarui.');
    }

    public function destroy(Driver $driver, PublicImageStorage $images): RedirectResponse
    {
        if ($driver->ratings()->exists()) {
            $driver->update(['status' => Driver::STATUS_INACTIVE]);

            return back()->with('status', 'Pegawai sudah memiliki rating, jadi dinonaktifkan.');
        }

        $driver->delete();
        $images->deleteMany([$driver->photo, $driver->sim_photo]);

        return redirect()->route('admin.employees.index')->with('status', 'Pegawai berhasil dihapus.');
    }
}
