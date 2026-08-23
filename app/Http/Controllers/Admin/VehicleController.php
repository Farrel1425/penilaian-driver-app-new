<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VehicleRequest;
use App\Models\Branch;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = Vehicle::query()
            ->with('branch')
            ->withCount('ratings')
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('police_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('color', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('branch_id'), fn ($query, int $branchId) => $query->where('branch_id', $branchId))
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.vehicles.index', ['vehicles' => $vehicles, 'branches' => Branch::query()->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.vehicles.create', ['vehicle' => new Vehicle(), 'branches' => Branch::query()->orderBy('name')->get()]);
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');
        $data['photo'] = $this->storePhoto($request);

        $vehicle = Vehicle::query()->create($data + ['qr_token' => Str::random(40)]);

        return redirect()->route('admin.vehicles.show', $vehicle)->with('status', 'Kendaraan berhasil dibuat.');
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load('branch')->loadCount('ratings');

        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function edit(Request $request, Vehicle $vehicle): View
    {
        return view('admin.vehicles.edit', [
            'vehicle' => $vehicle,
            'branches' => Branch::query()->orderBy('name')->get(),
            'returnTo' => $request->string('return_to')->toString() === 'detail' ? 'detail' : 'index',
        ]);
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $this->deletePhoto($vehicle->photo);
            $data['photo'] = $this->storePhoto($request);
        }

        $vehicle->update($data);

        if ($request->input('return_to') === 'detail') {
            return redirect()->route('admin.vehicles.show', $vehicle)->with('status', 'Kendaraan berhasil diperbarui.');
        }

        return redirect()->route('admin.vehicles.index')->with('status', 'Kendaraan berhasil diperbarui.');
    }

    public function regenerateQrToken(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update(['qr_token' => Str::random(40)]);

        return back()->with('status', 'QR token kendaraan berhasil diperbarui.');
    }

    public function toggleStatus(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update(['status' => $vehicle->status === Vehicle::STATUS_ACTIVE ? Vehicle::STATUS_INACTIVE : Vehicle::STATUS_ACTIVE]);

        return back()->with('status', 'Status kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->ratings()->exists()) {
            $vehicle->update(['status' => Vehicle::STATUS_INACTIVE]);

            return back()->with('status', 'Kendaraan sudah memiliki rating, jadi dinonaktifkan.');
        }

        $this->deletePhoto($vehicle->photo);
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('status', 'Kendaraan berhasil dihapus.');
    }

    private function storePhoto(VehicleRequest $request): ?string
    {
        return $request->hasFile('photo')
            ? $request->file('photo')->store('vehicles', 'public')
            : null;
    }

    private function deletePhoto(?string $photo): void
    {
        if ($photo && ! Str::startsWith($photo, ['http://', 'https://', '/'])) {
            Storage::disk('public')->delete($photo);
        }
    }
}
