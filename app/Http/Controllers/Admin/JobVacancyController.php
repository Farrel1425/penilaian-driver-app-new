<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JobVacancyRequest;
use App\Models\Branch;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobVacancyController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(array_keys(JobVacancy::STATUSES))],
            'period' => ['nullable', 'integer', 'exists:recruitment_periods,id'],
        ]);

        $vacancies = JobVacancy::query()
            ->with(['period', 'branches:id,name'])
            ->withCount('applications')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('work_type', 'like', "%{$search}%");
            }))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['period'] ?? null, fn ($query, int $period) => $query->where('recruitment_period_id', $period))
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.job-vacancies.index', [
            'vacancies' => $vacancies,
            'periods' => RecruitmentPeriod::query()->latest('id')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->formView('admin.job-vacancies.create', new JobVacancy);
    }

    public function store(JobVacancyRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('branch_ids');

        DB::transaction(function () use ($data, $request): void {
            $lastSortOrder = JobVacancy::query()
                ->where('recruitment_period_id', $data['recruitment_period_id'])
                ->orderByDesc('sort_order')
                ->lockForUpdate()
                ->value('sort_order');

            $data['sort_order'] = ((int) $lastSortOrder) + 1;
            $data['status'] = JobVacancy::STATUS_OPEN;
            $vacancy = JobVacancy::query()->create($data);
            $vacancy->branches()->sync($request->validated('branch_ids'));
        });

        return redirect()->route('admin.recruitment.index')->with('status', 'Lowongan berhasil dibuat.');
    }

    public function edit(JobVacancy $jobVacancy): View
    {
        $jobVacancy->load('branches:id');

        return $this->formView('admin.job-vacancies.edit', $jobVacancy);
    }

    public function update(JobVacancyRequest $request, JobVacancy $jobVacancy): RedirectResponse
    {
        $data = $request->safe()->except('branch_ids');

        DB::transaction(function () use ($data, $request, $jobVacancy): void {
            $jobVacancy->update($data);
            $jobVacancy->branches()->sync($request->validated('branch_ids'));
        });

        return redirect()->route('admin.recruitment.index')->with('status', 'Lowongan berhasil diperbarui.');
    }

    public function destroy(JobVacancy $jobVacancy): RedirectResponse
    {
        if ($jobVacancy->applications()->exists()) {
            $jobVacancy->update(['status' => JobVacancy::STATUS_CLOSED]);

            return back()->with('status', 'Lowongan sudah memiliki pelamar sehingga ditutup, bukan dihapus.');
        }

        $jobVacancy->delete();

        return redirect()->route('admin.recruitment.index')->with('status', 'Lowongan berhasil dihapus.');
    }

    public function updateActive(Request $request, JobVacancy $jobVacancy): RedirectResponse
    {
        $request->validate(['is_active' => ['nullable', 'boolean']]);

        $isActive = $request->boolean('is_active');
        $jobVacancy->update([
            'status' => $isActive ? JobVacancy::STATUS_OPEN : JobVacancy::STATUS_CLOSED,
        ]);

        return back()->with('status', $isActive
            ? 'Lowongan berhasil dibuka.'
            : 'Lowongan berhasil ditutup.');
    }

    private function formView(string $view, JobVacancy $vacancy): View
    {
        return view($view, [
            'vacancy' => $vacancy,
            'periods' => RecruitmentPeriod::query()->latest('id')->get(),
            'branches' => Branch::query()->active()->orderBy('name')->get(),
        ]);
    }
}
