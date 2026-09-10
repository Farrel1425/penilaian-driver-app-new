<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DriverRequest;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\EmployeeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

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

    public function store(DriverRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'sim_photo']);
        $data['employee_category_id'] ??= EmployeeCategory::query()
            ->where('name', 'Driver')
            ->value('id');
        $data['photo'] = $this->storeImage($request, 'photo', 'drivers');
        $data['sim_photo'] = $this->storeImage($request, 'sim_photo', 'driver-sims');

        $driver = Driver::query()->create($data);

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

    public function update(DriverRequest $request, Driver $driver): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'sim_photo']);
        $data['employee_category_id'] ??= $driver->employee_category_id;

        if ($request->hasFile('photo')) {
            $this->deletePhoto($driver->photo);
            $data['photo'] = $this->storeImage($request, 'photo', 'drivers');
        }

        if ($request->hasFile('sim_photo')) {
            $this->deletePhoto($driver->sim_photo);
            $data['sim_photo'] = $this->storeImage($request, 'sim_photo', 'driver-sims');
        }

        $driver->update($data);

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

    public function destroy(Driver $driver): RedirectResponse
    {
        if ($driver->ratings()->exists()) {
            $driver->update(['status' => Driver::STATUS_INACTIVE]);

            return back()->with('status', 'Pegawai sudah memiliki rating, jadi dinonaktifkan.');
        }

        $this->deletePhoto($driver->photo);
        $this->deletePhoto($driver->sim_photo);
        $driver->delete();

        return redirect()->route('admin.employees.index')->with('status', 'Pegawai berhasil dihapus.');
    }

    private function storeImage(DriverRequest $request, string $input, string $directory): ?string
    {
        return $request->hasFile($input)
            ? $request->file($input)->store($directory, 'public')
            : null;
    }

    private function deletePhoto(?string $photo): void
    {
        if ($photo && ! Str::startsWith($photo, ['http://', 'https://', '/'])) {
            Storage::disk('public')->delete($photo);
        }
    }
}
