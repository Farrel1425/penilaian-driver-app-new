<?php

namespace App\Http\Requests\Admin;

use App\Models\EmployeeCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $category = $this->route('employee_category');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique(EmployeeCategory::class)->ignore($category)],
            'requires_sim' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in([EmployeeCategory::STATUS_ACTIVE, EmployeeCategory::STATUS_INACTIVE])],
        ];
    }
}
