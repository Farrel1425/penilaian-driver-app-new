@php($field = "answers[{$question->id}]")
@php($isWizard = $wizard ?? false)
@php($isScroll = $scroll ?? false)
<fieldset @class(["passenger-assessment-question", "passenger-wizard-question" => $isWizard, "passenger-scroll-question" => $isScroll]) data-wizard-question data-required="{{ $question->is_required ? 'true' : 'false' }}" @if($question->target_type === App\Models\Question::TARGET_FEEDBACK && $question->answer_type === App\Models\Question::TYPE_PARAGRAPH) data-feedback-followup hidden @endif>
    @if ($question->icon_path)
        <div class="passenger-question-icon"><img src="{{ Str::startsWith($question->icon_path, ['http://', 'https://', '/']) ? $question->icon_path : asset('storage/'.$question->icon_path) }}" alt="" aria-hidden="true"></div>
    @endif

    @if ($isScroll)
        <legend class="passenger-scroll-legend">{{ $question->question }}</legend>
        @if ($question->indicator)<p class="passenger-scroll-indicator">{{ $question->indicator }}</p>@endif
        <p class="passenger-scroll-question-text">{{ $question->question }} @if($question->is_required)<b>*</b>@endif</p>
    @else
        <legend><span>{{ $number }}.</span> {{ $question->question }} @if($question->is_required)<b>*</b>@endif</legend>
    @endif

    @if ($question->instruction)<p class="passenger-question-instruction">{{ $question->instruction }}</p>@endif
    @error("answers.{$question->id}")<small class="passenger-assessment-error" data-wizard-server-error>{{ $message }}</small>@enderror
    <small class="passenger-assessment-error passenger-wizard-error" data-wizard-error hidden>Pertanyaan ini wajib dijawab.</small>

    @if($question->answer_type === App\Models\Question::TYPE_RATING)
        <div @class(['passenger-star-rating', 'passenger-wizard-rating' => $isWizard]) data-star-rating>
            @foreach([1, 2, 3, 4, 5] as $value)
                <label class="{{ (int) old("answers.{$question->id}", 0) >= $value ? 'is-selected' : '' }}">
                    <input type="radio" name="{{ $field }}" value="{{ $value }}" @checked((string) old("answers.{$question->id}") === (string) $value)>
                    @if($isWizard)<span>{{ $value }}</span>@endif
                    <x-lucide-star aria-label="{{ $value }} dari 5" />
                </label>
            @endforeach
        </div>
    @elseif($question->answer_type === App\Models\Question::TYPE_YES_NO)
        <div class="passenger-answer-options passenger-answer-yes-no">
            <label><input type="radio" name="{{ $field }}" value="1" @checked(old("answers.{$question->id}") === '1')><span>Ya</span></label>
            <label><input type="radio" name="{{ $field }}" value="0" @checked(old("answers.{$question->id}") === '0')><span>Tidak</span></label>
        </div>
    @elseif($question->answer_type === App\Models\Question::TYPE_MULTIPLE_CHOICE)
        <div class="passenger-answer-options">
            @foreach($question->options as $option)
                <label><input type="radio" name="{{ $field }}" value="{{ $option->id }}" @if($question->target_type === App\Models\Question::TARGET_FEEDBACK) data-feedback-choice data-feedback-empty="{{ $option->sort_order === 1 ? 'true' : 'false' }}" @endif @checked((string) old("answers.{$question->id}") === (string) $option->id)><span>{{ $option->option_text }}</span></label>
            @endforeach
        </div>
    @elseif($question->answer_type === App\Models\Question::TYPE_CHECKBOX)
        <div class="passenger-answer-options">
            @foreach($question->options as $option)
                <label><input type="checkbox" name="{{ $field }}[]" value="{{ $option->id }}" @checked(in_array((string) $option->id, array_map('strval', old("answers.{$question->id}", [])), true))><span>{{ $option->option_text }}</span></label>
            @endforeach
        </div>
    @elseif($question->answer_type === App\Models\Question::TYPE_PARAGRAPH)
        <textarea name="{{ $field }}" rows="4" placeholder="{{ $question->placeholder ?: 'Tulis jawaban Anda...' }}">{{ old("answers.{$question->id}") }}</textarea>
    @else
        <input type="text" name="{{ $field }}" value="{{ old("answers.{$question->id}") }}" placeholder="{{ $question->placeholder ?: 'Tulis jawaban Anda...' }}">
    @endif
</fieldset>