<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BranchRequest;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $branches = Branch::query()
            ->withCount(['drivers', 'vehicles'])
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('regency', 'like', "%{$search}%")
                        ->orWhere('pic_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('admin.branches.create', ['branch' => new Branch()]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        $branch = Branch::query()->create($request->validated());

        return redirect()->route('admin.branches.show', $branch)->with('status', 'Cabang berhasil dibuat.');
    }

    public function show(Branch $branch): View
    {
        $branch->loadCount(['drivers', 'vehicles', 'ratings']);

        return view('admin.branches.show', compact('branch'));
    }

    public function edit(Request $request, Branch $branch): View
    {
        return view('admin.branches.edit', [
            'branch' => $branch,
            'returnTo' => $request->string('return_to')->toString() === 'detail' ? 'detail' : 'index',
        ]);
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());

        if ($request->input('return_to') === 'detail') {
            return redirect()->route('admin.branches.show', $branch)->with('status', 'Cabang berhasil diperbarui.');
        }

        return redirect()->route('admin.branches.index')->with('status', 'Cabang berhasil diperbarui.');
    }

    public function toggleStatus(Branch $branch): RedirectResponse
    {
        $branch->update(['status' => $branch->status === Branch::STATUS_ACTIVE ? Branch::STATUS_INACTIVE : Branch::STATUS_ACTIVE]);

        return back()->with('status', 'Status cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        if ($branch->drivers()->exists() || $branch->vehicles()->exists() || $branch->ratings()->exists()) {
            $branch->update(['status' => Branch::STATUS_INACTIVE]);

            return back()->with('status', 'Cabang sudah punya data terkait, jadi dinonaktifkan.');
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('status', 'Cabang berhasil dihapus.');
    }
}
