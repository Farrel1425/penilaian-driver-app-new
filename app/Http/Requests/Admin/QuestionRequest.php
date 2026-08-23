<?php

namespace App\Http\Requests\Admin;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('target_type') === Question::TARGET_VEHICLE) {
            $this->merge(['indicator' => Question::VEHICLE_INDICATOR]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:1000'],
            'indicator' => ['required', 'string', 'max:255'],
            'target_type' => ['required', Rule::in([Question::TARGET_DRIVER, Question::TARGET_VEHICLE])],
            'answer_type' => ['required', Rule::in([
                Question::TYPE_RATING,
                Question::TYPE_YES_NO,
                Question::TYPE_MULTIPLE_CHOICE,
                Question::TYPE_CHECKBOX,
                Question::TYPE_SHORT_TEXT,
                Question::TYPE_PARAGRAPH,
            ])],
            'is_required' => ['required', 'boolean'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::in([Question::STATUS_ACTIVE, Question::STATUS_INACTIVE])],
            'options' => ['nullable', 'array'],
            'options.*.option_text' => ['nullable', 'string', 'max:255'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (in_array($this->input('answer_type'), [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_CHECKBOX], true)
                && count($this->normalizedOptions()) < 1) {
                $validator->errors()->add('options', 'Opsi jawaban wajib diisi untuk tipe pilihan.');
            }

            if ($validator->errors()->hasAny(['target_type', 'weight'])) {
                return;
            }

            $targetType = $this->string('target_type')->toString();
            $currentQuestion = $this->route('question');
            $currentQuestionId = $currentQuestion instanceof Question ? $currentQuestion->id : null;
            $existingWeight = Question::query()
                ->where('target_type', $targetType)
                ->when($currentQuestionId, fn ($query) => $query->whereKeyNot($currentQuestionId))
                ->sum('weight');
            $totalWeight = $existingWeight + (int) $this->input('weight');

            if ($totalWeight > 100) {
                $remaining = max(0, 100 - $existingWeight);
                $validator->errors()->add('weight', "Bobot melebihi 100%. Sisa bobot {$remaining}%.");
            }

            if ($this->input('status') === Question::STATUS_ACTIVE && $totalWeight !== 100) {
                $targetLabel = $targetType === Question::TARGET_DRIVER ? 'Driver' : 'Kendaraan';
                $validator->errors()->add('status', "Pertanyaan {$targetLabel} hanya dapat aktif bila total bobot tepat 100%. Total saat ini {$totalWeight}%.");
            }
        });
    }

    public function questionData(): array
    {
        return $this->safe()->only(['question', 'indicator', 'target_type', 'answer_type', 'is_required', 'weight', 'status']);
    }

    public function normalizedOptions(): array
    {
        return collect($this->input('options', []))
            ->map(fn (array $option, int $index): array => [
                'option_text' => trim((string) ($option['option_text'] ?? '')),
                'sort_order' => (int) ($option['sort_order'] ?? $index + 1),
            ])
            ->filter(fn (array $option): bool => $option['option_text'] !== '')
            ->values()
            ->all();
    }
}
