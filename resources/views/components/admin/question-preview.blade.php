@props(['question' => null, 'options' => collect()])

@php
    $text = old('question', $question?->question ?: 'Bagaimana keramahan driver?');
    $answerType = old('answer_type', $question?->answer_type ?: App\Models\Question::TYPE_RATING);
    $optionValues = collect(old('options', $options->map(fn ($option) => ['option_text' => $option->option_text, 'sort_order' => $option->sort_order])->all()))
        ->pluck('option_text')
        ->filter()
        ->values();
    $instruction = old('instruction', $question?->instruction);
    $placeholder = old('placeholder', $question?->placeholder);
    $ratingMinLabel = old('rating_min_label', $question?->rating_min_label ?: 'Sangat Buruk');
    $ratingMaxLabel = old('rating_max_label', $question?->rating_max_label ?: 'Sangat Baik');
    $iconUrl = $question?->icon_path
        ? (Str::startsWith($question->icon_path, ['http://', 'https://', '/']) ? $question->icon_path : asset('storage/'.$question->icon_path))
        : null;
@endphp

<aside class="question-preview" data-question-preview>
    <div class="question-preview-heading">
        <p class="eyebrow">Preview Penumpang</p>
        <span>Mobile</span>
    </div>

    <div class="question-preview-device">
        <header class="question-preview-mobile-header">
            <x-lucide-chevron-left aria-hidden="true" />
            <strong>Penilaian</strong>
        </header>

        <div class="question-preview-assessment">
            <p class="passenger-assessment-intro">Berikan penilaian terbaik Anda</p>
            <section class="passenger-assessment-group">
                <h2 data-preview-target>{{ $question?->target_type === App\Models\Question::TARGET_VEHICLE ? 'Penilaian Kendaraan' : 'Penilaian Driver' }}</h2>
                <fieldset class="passenger-assessment-question question-preview-question">
                    <div class="passenger-question-icon" data-preview-icon @if (! $iconUrl) hidden @endif>
                        @if ($iconUrl)<img src="{{ $iconUrl }}" alt="Ikon pertanyaan">@endif
                    </div>
                    <legend><span>1.</span> <span data-preview-question>{{ $text }}</span></legend>
                    <p class="passenger-question-instruction" data-preview-instruction @if (blank($instruction)) hidden @endif>{{ $instruction }}</p>
                    <div data-preview-body>
                        @include('admin.questions._preview-body', ['answerType' => $answerType, 'options' => $optionValues, 'placeholder' => $placeholder, 'ratingMinLabel' => $ratingMinLabel, 'ratingMaxLabel' => $ratingMaxLabel])
                    </div>
                </fieldset>
            </section>
        </div>
        <footer class="question-preview-footer">
            <button type="button" disabled>Kirim Penilaian</button>
        </footer>
    </div>
</aside>
