<?php

namespace App\Http\Requests\Admin;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $branch = $this->route('branch');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch?->id)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'regency' => ['required', Rule::in(Branch::BALI_REGENCIES)],
            'pic_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:8', 'max:30', 'regex:/^[0-9+()\\-\\s]+$/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'status' => ['required', Rule::in([Branch::STATUS_ACTIVE, Branch::STATUS_INACTIVE])],
        ];
    }
}
