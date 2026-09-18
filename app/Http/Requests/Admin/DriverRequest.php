<?php

namespace App\Http\Requests\Admin;

use App\Models\Branch;
use App\Models\Driver;
use App\Models\EmployeeCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', Rule::exists(Branch::class, 'id')],
            'employee_category_id' => ['nullable', Rule::exists(EmployeeCategory::class, 'id')],
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'min:8', 'max:30', 'regex:/^[0-9+()\\-\\s]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'marital_status' => ['nullable', Rule::in(array_keys(Driver::MARITAL_STATUSES))],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_photo' => ['sometimes', 'boolean'],
            'remove_photo' => ['sometimes', 'boolean'],
            'sim_number' => ['nullable', 'string', 'max:100'],
            'sim_type' => ['nullable', Rule::in(array_keys(Driver::SIM_TYPES))],
            'sim_expired_at' => ['nullable', 'date'],
            'sim_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_sim_photo' => ['sometimes', 'boolean'],
            'remove_sim_photo' => ['sometimes', 'boolean'],
            'join_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:join_date'],
            'status' => ['required', Rule::in([Driver::STATUS_ACTIVE, Driver::STATUS_INACTIVE])],
        ];
    }
}
