<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecruitmentPeriodRequest;
use App\Models\RecruitmentPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecruitmentPeriodController extends Controller
{
    public function index(): View
    {
        return view('admin.recruitment-periods.index', [
            'periods' => RecruitmentPeriod::query()->withCount('vacancies')->latest('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.recruitment-periods.create', ['period' => new RecruitmentPeriod]);
    }

    public function store(RecruitmentPeriodRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['starts_at'] = today();
        $data['ends_at'] = null;
        $data['is_active'] = true;

        DB::transaction(function () use ($data): void {
            RecruitmentPeriod::query()->update(['is_active' => false]);
            RecruitmentPeriod::query()->create($data);
        });

        return redirect()->route('admin.recruitment.index')->with('status', 'Gelombang recruitment berhasil dibuat.');
    }

    public function edit(RecruitmentPeriod $recruitmentPeriod): View
    {
        return view('admin.recruitment-periods.edit', ['period' => $recruitmentPeriod]);
    }

    public function update(RecruitmentPeriodRequest $request, RecruitmentPeriod $recruitmentPeriod): RedirectResponse
    {
        $data = $request->validated();
        $recruitmentPeriod->update($data);

        return redirect()->route('admin.recruitment.index')->with('status', 'Gelombang recruitment berhasil diperbarui.');
    }

    public function updateActive(Request $request, RecruitmentPeriod $recruitmentPeriod): RedirectResponse
    {
        $request->validate(['is_active' => ['nullable', 'boolean']]);

        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($isActive, $recruitmentPeriod): void {
            if ($isActive) {
                RecruitmentPeriod::query()
                    ->where('id', '!=', $recruitmentPeriod->id)
                    ->update(['is_active' => false]);
            }

            $recruitmentPeriod->update(['is_active' => $isActive]);
        });

        return back()->with('status', $isActive
            ? 'Gelombang recruitment berhasil diaktifkan.'
            : 'Gelombang recruitment berhasil dinonaktifkan.');
    }

    public function destroy(RecruitmentPeriod $recruitmentPeriod): RedirectResponse
    {
        if ($recruitmentPeriod->vacancies()->exists()) {
            return back()->with('error', 'Gelombang yang sudah memiliki lowongan tidak dapat dihapus. Nonaktifkan gelombang jika sudah selesai.');
        }

        $recruitmentPeriod->delete();

        return redirect()->route('admin.recruitment.index')->with('status', 'Gelombang recruitment berhasil dihapus.');
    }
}
