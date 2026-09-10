<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnershipInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:150'],
            'contact_name' => ['required', 'string', 'max:150'],
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'service' => ['required', 'string', 'max:150'],
            'estimated_need' => ['nullable', 'string', 'max:150'],
            'contract_duration' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => 'nama perusahaan atau instansi',
            'contact_name' => 'nama PIC dan jabatan',
            'whatsapp' => 'nomor WhatsApp PIC',
            'email' => 'email resmi',
            'service' => 'kebutuhan layanan',
            'estimated_need' => 'estimasi kebutuhan',
            'contract_duration' => 'durasi kontrak',
            'notes' => 'keterangan tambahan',
        ];
    }
}
