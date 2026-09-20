<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(array_keys(JobApplication::STATUSES))],
            'vacancy' => ['nullable', 'integer', 'exists:job_vacancies,id'],
        ]);

        $applications = JobApplication::query()
            ->with(['vacancy:id,title', 'branch:id,name'])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['vacancy'] ?? null, fn ($query, int $vacancy) => $query->where('job_vacancy_id', $vacancy))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.job-applications.index', [
            'applications' => $applications,
            'vacancies' => JobVacancy::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function show(JobApplication $jobApplication): View
    {
        $jobApplication->load(['vacancy.period', 'branch']);

        return view('admin.job-applications.show', ['application' => $jobApplication]);
    }

    public function updateStatus(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(JobApplication::STATUSES))],
        ]);

        $jobApplication->update($validated);

        return back()->with('status', 'Status pelamar berhasil diperbarui.');
    }

    public function download(JobApplication $jobApplication): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($jobApplication->document_path), 404);

        $filename = 'lamaran-'.str($jobApplication->full_name)->slug().'-'.$jobApplication->id.'.pdf';

        return Storage::disk('local')->download($jobApplication->document_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
