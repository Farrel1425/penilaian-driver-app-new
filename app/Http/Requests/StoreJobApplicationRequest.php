<?php

namespace App\Http\Requests;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nik' => preg_replace('/\D+/', '', (string) $this->input('nik')),
            'whatsapp' => trim((string) $this->input('whatsapp')),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:180'],
            'nik' => ['required', 'digits:16'],
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'job_vacancy_id' => ['required', 'integer'],
            'domicile' => ['required', 'string', 'max:180'],
            'branch_id' => ['required', 'integer'],
            'experience' => ['nullable', 'string', 'max:2000'],
            'document' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
            'consent' => ['accepted'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['job_vacancy_id', 'branch_id', 'nik'])) {
                return;
            }

            $vacancy = JobVacancy::query()
                ->publiclyAvailable()
                ->with(['branches' => fn ($query) => $query->active()->select('branches.id')])
                ->find($this->integer('job_vacancy_id'));

            if (! $vacancy) {
                $validator->errors()->add('job_vacancy_id', 'Lowongan sudah tidak tersedia.');

                return;
            }

            if (! $vacancy->branches->contains('id', $this->integer('branch_id'))) {
                $validator->errors()->add('branch_id', 'Cabang penempatan tidak tersedia untuk lowongan ini.');
            }

            $duplicate = JobApplication::query()
                ->where('job_vacancy_id', $vacancy->id)
                ->where('nik_hash', JobApplication::nikHash((string) $this->input('nik')))
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('nik', 'NIK ini sudah pernah mengirim lamaran untuk posisi tersebut.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'nama lengkap',
            'nik' => 'NIK',
            'whatsapp' => 'nomor WhatsApp',
            'job_vacancy_id' => 'formasi yang dilamar',
            'domicile' => 'domisili saat ini',
            'branch_id' => 'cabang penempatan',
            'document' => 'berkas lamaran',
            'consent' => 'persetujuan',
        ];
    }
}
