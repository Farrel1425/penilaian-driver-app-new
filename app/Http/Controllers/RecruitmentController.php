<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class RecruitmentController extends Controller
{
    public function index(): View
    {
        $period = RecruitmentPeriod::query()
            ->active()
            ->latest('starts_at')
            ->latest('id')
            ->first();

        $vacancies = JobVacancy::query()
            ->publiclyAvailable()
            ->when($period, fn ($query) => $query->whereBelongsTo($period, 'period'))
            ->with(['branches' => fn ($query) => $query->active()->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('recruitment.index', [
            'period' => $period,
            'vacancies' => $vacancies,
            'recruitmentWhatsapp' => SystemSetting::recruitmentWhatsapp(),
            'recruitmentWhatsappUrl' => SystemSetting::recruitmentWhatsappUrl(),
        ]);
    }

    public function store(StoreJobApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $document = $request->file('document');
        $path = $document->store('recruitment-applications/'.now()->format('Y/m'), 'local');

        try {
            JobApplication::query()->create([
                'job_vacancy_id' => $validated['job_vacancy_id'],
                'branch_id' => $validated['branch_id'],
                'full_name' => $validated['full_name'],
                'nik' => $validated['nik'],
                'nik_hash' => JobApplication::nikHash($validated['nik']),
                'whatsapp' => $validated['whatsapp'],
                'email' => $validated['email'],
                'domicile' => $validated['domicile'],
                'experience' => $validated['experience'] ?? null,
                'document_path' => $path,
                'document_original_name' => $document->getClientOriginalName(),
                'consent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('recruitment.index')
            ->with('application_status', 'Lamaran berhasil dikirim. Tim HRD PT. BDS akan menghubungi Anda melalui WhatsApp jika lolos ke tahap berikutnya.');
    }
}
