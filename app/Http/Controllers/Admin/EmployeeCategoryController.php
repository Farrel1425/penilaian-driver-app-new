<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmployeeCategoryRequest;
use App\Models\EmployeeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = EmployeeCategory::query()
            ->withCount('employees')
            ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.employee-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.employee-categories.create', ['category' => new EmployeeCategory]);
    }

    public function store(EmployeeCategoryRequest $request): RedirectResponse
    {
        EmployeeCategory::query()->create($request->validated());

        return redirect()->route('admin.employee-categories.index')->with('status', 'Kategori pegawai berhasil dibuat.');
    }

    public function edit(EmployeeCategory $employeeCategory): View
    {
        return view('admin.employee-categories.edit', ['category' => $employeeCategory]);
    }

    public function update(EmployeeCategoryRequest $request, EmployeeCategory $employeeCategory): RedirectResponse
    {
        $employeeCategory->update($request->validated());

        return redirect()->route('admin.employee-categories.index')->with('status', 'Kategori pegawai berhasil diperbarui.');
    }

    public function toggleStatus(EmployeeCategory $employeeCategory): RedirectResponse
    {
        $employeeCategory->update([
            'status' => $employeeCategory->status === EmployeeCategory::STATUS_ACTIVE
                ? EmployeeCategory::STATUS_INACTIVE
                : EmployeeCategory::STATUS_ACTIVE,
        ]);

        return back()->with('status', 'Status kategori pegawai berhasil diperbarui.');
    }

    public function destroy(EmployeeCategory $employeeCategory): RedirectResponse
    {
        if ($employeeCategory->employees()->exists()) {
            $employeeCategory->update(['status' => EmployeeCategory::STATUS_INACTIVE]);

            return back()->with('status', 'Kategori masih dipakai pegawai, sehingga dinonaktifkan.');
        }

        $employeeCategory->delete();

        return redirect()->route('admin.employee-categories.index')->with('status', 'Kategori pegawai berhasil dihapus.');
    }
}
