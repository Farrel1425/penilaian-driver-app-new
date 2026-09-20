<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentManagementController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(['vacancies', 'applicants'])],
            'search' => ['nullable', 'string', 'max:150'],
            'vacancy_status' => ['nullable', Rule::in(array_keys(JobVacancy::STATUSES))],
            'applicant_status' => ['nullable', Rule::in(array_keys(JobApplication::STATUSES))],
            'applicant_vacancy' => ['nullable', 'integer', 'exists:job_vacancies,id'],
        ]);

        $vacancyFilter = function ($query) use ($filters): void {
            $query
                ->with(['branches' => fn ($query) => $query->orderBy('name')])
                ->withCount('applications')
                ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('work_type', 'like', "%{$search}%");
                }))
                ->when($filters['vacancy_status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
                ->orderBy('sort_order')
                ->orderBy('title');
        };

        $periods = RecruitmentPeriod::query()
            ->with(['vacancies' => $vacancyFilter])
            ->withCount('vacancies')
            ->withSum('vacancies as total_quota', 'quota')
            ->orderByDesc('is_active')
            ->orderByDesc('starts_at')
            ->latest('id')
            ->get();

        $applications = JobApplication::query()
            ->with(['vacancy.period', 'branch'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($filters['applicant_status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['applicant_vacancy'] ?? null, fn (Builder $query, int $vacancy) => $query->where('job_vacancy_id', $vacancy))
            ->latest()
            ->paginate(10, ['*'], 'applicant_page')
            ->withQueryString();

        return view('admin.recruitment.index', [
            'activeTab' => ($filters['tab'] ?? 'vacancies') === 'applicants' ? 'applicants' : 'vacancies',
            'applicationCount' => JobApplication::query()->count(),
            'applications' => $applications,
            'branches' => Branch::query()->active()->orderBy('name')->get(),
            'filters' => $filters,
            'periods' => $periods,
            'vacancies' => JobVacancy::query()->with('period:id,name')->orderBy('title')->get(),
        ]);
    }
}
