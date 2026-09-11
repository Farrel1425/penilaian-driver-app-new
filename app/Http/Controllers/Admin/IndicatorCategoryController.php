<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndicatorCategoryRequest;
use App\Models\IndicatorCategory;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndicatorCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = IndicatorCategory::query()
            ->withCount('questions')
            ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($request->string('target_type')->toString(), fn ($query, string $target) => $query->where('target_type', $target))
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.indicator-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.indicator-categories.create', [
            'category' => new IndicatorCategory([
                'target_type' => Question::TARGET_DRIVER,
                'status' => IndicatorCategory::STATUS_ACTIVE,
                'sort_order' => ((int) IndicatorCategory::query()->max('sort_order')) + 1,
            ]),
        ]);
    }

    public function store(IndicatorCategoryRequest $request): RedirectResponse
    {
        IndicatorCategory::query()->create($request->validated());

        return redirect()->route('admin.indicator-categories.index')->with('status', 'Kategori indikator berhasil dibuat.');
    }

    public function edit(IndicatorCategory $indicatorCategory): View
    {
        return view('admin.indicator-categories.edit', ['category' => $indicatorCategory]);
    }

    public function update(IndicatorCategoryRequest $request, IndicatorCategory $indicatorCategory): RedirectResponse
    {
        if ($indicatorCategory->questions()->exists()
            && $request->string('target_type')->toString() !== $indicatorCategory->target_type) {
            return back()->withInput()->with('error', 'Target kategori tidak dapat diubah karena sudah digunakan oleh pertanyaan.');
        }

        $indicatorCategory->update($request->validated());

        return redirect()->route('admin.indicator-categories.index')->with('status', 'Kategori indikator berhasil diperbarui.');
    }

    public function toggleStatus(IndicatorCategory $indicatorCategory): RedirectResponse
    {
        $indicatorCategory->update([
            'status' => $indicatorCategory->status === IndicatorCategory::STATUS_ACTIVE
                ? IndicatorCategory::STATUS_INACTIVE
                : IndicatorCategory::STATUS_ACTIVE,
        ]);

        return back()->with('status', 'Status kategori indikator berhasil diperbarui.');
    }

    public function destroy(IndicatorCategory $indicatorCategory): RedirectResponse
    {
        if ($indicatorCategory->questions()->exists()) {
            $indicatorCategory->update(['status' => IndicatorCategory::STATUS_INACTIVE]);

            return back()->with('status', 'Kategori masih dipakai pertanyaan, sehingga dinonaktifkan.');
        }

        $indicatorCategory->delete();

        return redirect()->route('admin.indicator-categories.index')->with('status', 'Kategori indikator berhasil dihapus.');
    }
}
