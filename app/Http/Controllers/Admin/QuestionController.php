<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuestionRequest;
use App\Models\IndicatorCategory;
use App\Models\Question;
use App\Services\PublicImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $isReordering = $request->boolean('reorder');
        $query = Question::query()->withCount('options')->ordered();

        if ($isReordering) {
            $questions = $query->get();
        } else {
            $questions = $query
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where(fn ($query) => $query
                    ->where('question', 'like', "%{$search}%")
                    ->orWhereHas('indicatorCategory', fn ($query) => $query->where('name', 'like', "%{$search}%"))))
                ->when($request->string('target_type')->toString(), fn ($query, string $target) => $query->where('target_type', $target))
                ->when($request->string('answer_type')->toString(), fn ($query, string $type) => $query->where('answer_type', $type))
                ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
                ->paginate(10)
                ->withQueryString();
        }

        return view('admin.questions.index', compact('questions', 'isReordering'));
    }

    public function create(): View
    {
        return view('admin.questions.create', [
            'question' => new Question(['status' => Question::STATUS_INACTIVE]),
            'indicatorCategories' => IndicatorCategory::query()->active()->ordered()->get(),
            'weightSummary' => $this->weightSummary(),
        ]);
    }

    public function store(QuestionRequest $request, PublicImageStorage $images): RedirectResponse
    {
        $iconPath = $request->hasFile('icon') ? $images->store($request->file('icon'), 'question-icons') : null;

        try {
            $question = DB::transaction(function () use ($request, $iconPath): Question {
                $data = $request->questionData();
                $data['sort_order'] = ((int) Question::query()->max('sort_order')) + 1;
                $data['icon_path'] = $iconPath;
                $question = Question::query()->create($data);
                $this->syncOptions($question, $request->normalizedOptions());

                return $question;
            });
        } catch (Throwable $exception) {
            $images->delete($iconPath);
            throw $exception;
        }

        return redirect()->route('admin.questions.show', $question)->with('status', 'Pertanyaan berhasil dibuat.');
    }

    public function show(Question $question): View
    {
        $question->load('options')->loadCount('ratingAnswers');

        return view('admin.questions.show', compact('question'));
    }

    public function edit(Request $request, Question $question): View
    {
        $question->load('options');

        return view('admin.questions.edit', [
            'question' => $question,
            'indicatorCategories' => IndicatorCategory::query()
                ->where(fn ($query) => $query->active()->orWhere('id', $question->indicator_category_id))
                ->ordered()
                ->get(),
            'weightSummary' => $this->weightSummary($question),
            'returnTo' => $request->string('return_to')->toString() === 'detail' ? 'detail' : 'index',
        ]);
    }

    public function update(QuestionRequest $request, Question $question, PublicImageStorage $images): RedirectResponse
    {
        $oldIcon = null;
        $newIcon = null;
        if ($request->hasFile('icon')) {
            $oldIcon = $question->icon_path;
            $newIcon = $images->store($request->file('icon'), 'question-icons');
        } elseif ($request->boolean('remove_icon')) {
            $oldIcon = $question->icon_path;
        }

        try {
            DB::transaction(function () use ($request, $question, $newIcon, $oldIcon): void {
                $data = $request->questionData();

                if ($newIcon !== null) {
                    $data['icon_path'] = $newIcon;
                } elseif ($oldIcon !== null) {
                    $data['icon_path'] = null;
                }

                $question->update($data);
                $this->syncOptions($question, $request->normalizedOptions());
            });
        } catch (Throwable $exception) {
            $images->delete($newIcon);
            throw $exception;
        }
        $images->delete($oldIcon);

        if ($request->input('return_to') === 'detail') {
            return redirect()->route('admin.questions.show', $question)->with('status', 'Pertanyaan berhasil diperbarui.');
        }

        return redirect()->route('admin.questions.index')->with('status', 'Pertanyaan berhasil diperbarui.');
    }

    public function toggleStatus(Question $question): RedirectResponse
    {
        if (in_array($question->target_type, [Question::TARGET_DRIVER, Question::TARGET_VEHICLE], true)
            && $question->status === Question::STATUS_INACTIVE
            && $this->weightTotal($question->target_type) !== 100) {
            $targetLabel = Question::targetLabel($question->target_type);

            return back()->with('error', "Pertanyaan {$targetLabel} hanya dapat diaktifkan bila total bobot tepat 100%.");
        }

        $question->update([
            'status' => $question->status === Question::STATUS_ACTIVE
                ? Question::STATUS_INACTIVE
                : Question::STATUS_ACTIVE,
        ]);

        return back()->with('status', 'Status pertanyaan berhasil diperbarui.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'distinct', 'exists:questions,id'],
        ]);

        $order = array_map('intval', $data['order']);
        $currentIds = Question::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $submittedIds = $order;
        sort($currentIds);
        sort($submittedIds);

        if ($currentIds !== $submittedIds) {
            return back()->with('error', 'Urutan tidak dapat disimpan karena data pertanyaan telah berubah. Silakan muat ulang halaman.');
        }

        DB::transaction(function () use ($order): void {
            foreach ($order as $index => $questionId) {
                Question::query()->whereKey($questionId)->update(['sort_order' => $index + 1]);
            }
        });

        return redirect()->route('admin.questions.index')->with('status', 'Urutan pertanyaan berhasil diperbarui.');
    }

    public function destroy(Question $question, PublicImageStorage $images): RedirectResponse
    {
        if ($question->ratingAnswers()->exists()) {
            $question->update(['status' => Question::STATUS_INACTIVE]);

            return back()->with('status', 'Pertanyaan sudah dipakai pada rating, jadi dinonaktifkan.');
        }

        $question->delete();
        $images->delete($question->icon_path);

        return redirect()->route('admin.questions.index')->with('status', 'Pertanyaan berhasil dihapus.');
    }

    private function syncOptions(Question $question, array $options): void
    {
        $question->options()->delete();

        if (! in_array($question->answer_type, [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_CHECKBOX], true)) {
            return;
        }

        foreach ($options as $index => $option) {
            $question->options()->create([
                'option_text' => $option['option_text'],
                'sort_order' => $option['sort_order'] ?: $index + 1,
            ]);
        }
    }

    private function weightSummary(?Question $question = null): array
    {
        return [
            Question::TARGET_DRIVER => $this->weightTotal(Question::TARGET_DRIVER, $question),
            Question::TARGET_VEHICLE => $this->weightTotal(Question::TARGET_VEHICLE, $question),
        ];
    }

    private function weightTotal(string $targetType, ?Question $excluding = null): int
    {
        return (int) Question::query()
            ->where('target_type', $targetType)
            ->when($excluding && $excluding->target_type === $targetType, fn ($query) => $query->whereKeyNot($excluding->id))
            ->sum('weight');
    }
}
