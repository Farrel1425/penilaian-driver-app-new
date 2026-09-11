<?php

namespace App\Http\Requests\Admin;

use App\Models\IndicatorCategory;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndicatorCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $category = $this->route('indicator_category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('indicator_categories')->where('target_type', $this->input('target_type'))->ignore($category),
            ],
            'target_type' => ['required', Rule::in([
                Question::TARGET_DRIVER,
                Question::TARGET_VEHICLE,
                Question::TARGET_FEEDBACK,
            ])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', Rule::in([IndicatorCategory::STATUS_ACTIVE, IndicatorCategory::STATUS_INACTIVE])],
        ];
    }
}
