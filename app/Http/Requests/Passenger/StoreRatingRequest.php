<?php

namespace App\Http\Requests\Passenger;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable'],
        ];
    }

    public function validatedAnswers($questions): array
    {
        $input = $this->input('answers', []);
        $answers = [];
        $errors = [];

        foreach ($questions as $question) {
            $raw = $input[$question->id] ?? null;
            $missing = $raw === null || $raw === '' || $raw === [];

            if ($question->is_required && $missing) {
                $errors["answers.{$question->id}"] = 'Pertanyaan ini wajib dijawab.';

                continue;
            }

            if ($missing) {
                continue;
            }

            $answer = ['question_id' => $question->id, 'answer_value' => null, 'answer_text' => null];

            if ($question->answer_type === Question::TYPE_RATING) {
                if (! in_array((int) $raw, [1, 2, 3, 4, 5], true)) {
                    $errors["answers.{$question->id}"] = 'Rating harus bernilai 1 sampai 5.';

                    continue;
                }
                $answer['answer_value'] = [(int) $raw];
            } elseif ($question->answer_type === Question::TYPE_YES_NO) {
                if (! in_array((string) $raw, ['0', '1'], true)) {
                    $errors["answers.{$question->id}"] = 'Jawaban harus Ya atau Tidak.';

                    continue;
                }
                $answer['answer_value'] = [(int) $raw];
            } elseif ($question->answer_type === Question::TYPE_MULTIPLE_CHOICE) {
                $allowed = $question->options->pluck('id')->map(fn ($id) => (string) $id)->all();
                if (! in_array((string) $raw, $allowed, true)) {
                    $errors["answers.{$question->id}"] = 'Opsi jawaban tidak valid.';

                    continue;
                }
                $answer['answer_value'] = [(int) $raw];
            } elseif ($question->answer_type === Question::TYPE_CHECKBOX) {
                $values = is_array($raw) ? $raw : [$raw];
                $allowed = $question->options->pluck('id')->map(fn ($id) => (string) $id)->all();
                $invalid = collect($values)->contains(fn ($value) => ! in_array((string) $value, $allowed, true));
                if ($invalid) {
                    $errors["answers.{$question->id}"] = 'Opsi jawaban tidak valid.';

                    continue;
                }
                $answer['answer_value'] = collect($values)->map(fn ($value) => (int) $value)->values()->all();
            } elseif ($question->answer_type === Question::TYPE_SHORT_TEXT || $question->answer_type === Question::TYPE_PARAGRAPH) {
                $answer['answer_text'] = trim((string) $raw);
            }

            $answers[] = $answer;
        }

        $feedbackChoice = $questions->first(fn (Question $question): bool => $question->target_type === Question::TARGET_FEEDBACK
            && $question->answer_type === Question::TYPE_MULTIPLE_CHOICE);
        $feedbackFollowUp = $questions->first(fn (Question $question): bool => $question->target_type === Question::TARGET_FEEDBACK
            && $question->answer_type === Question::TYPE_PARAGRAPH);

        if ($feedbackChoice && $feedbackFollowUp && filled($input[$feedbackChoice->id] ?? null)) {
            $selectedOption = $feedbackChoice->options->firstWhere('id', (int) $input[$feedbackChoice->id]);
            $followUp = trim((string) ($input[$feedbackFollowUp->id] ?? ''));

            if ($selectedOption && $selectedOption->sort_order > 1 && $followUp === '') {
                $errors["answers.{$feedbackFollowUp->id}"] = 'Mohon jelaskan kondisi yang perlu ditindaklanjuti.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $answers;
    }
}
