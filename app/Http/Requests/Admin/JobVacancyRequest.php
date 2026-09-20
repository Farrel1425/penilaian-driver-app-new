<?php

namespace App\Http\Requests\Admin;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'recruitment_period_id' => ['required', 'integer', 'exists:recruitment_periods,id'],
            'category' => ['required', 'string', 'max:100'],
            'work_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:2000'],
            'qualification' => ['required', 'string', 'max:1000'],
            'compensation' => ['required', 'string', 'max:1000'],
            'quota' => ['required', 'integer', 'min:1', 'max:100000'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('branches', 'id')->where('status', Branch::STATUS_ACTIVE),
            ],
        ];
    }
}
