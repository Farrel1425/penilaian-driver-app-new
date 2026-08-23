@switch($answerType)
    @case(App\Models\Question::TYPE_RATING)
        <div class="passenger-star-rating" aria-label="Contoh rating 4 dari 5">
            @foreach ([1, 2, 3, 4, 5] as $value)
                <span @class(['is-selected' => $value <= 4])><x-lucide-star aria-hidden="true" /></span>
            @endforeach
        </div>
        <div class="passenger-rating-labels"><span>{{ $ratingMinLabel ?: 'Sangat Buruk' }}</span><span>{{ $ratingMaxLabel ?: 'Sangat Baik' }}</span></div>
        @break
    @case(App\Models\Question::TYPE_YES_NO)
        <div class="passenger-answer-options passenger-answer-yes-no"><label><input type="radio" disabled><span>Ya</span></label><label><input type="radio" disabled><span>Tidak</span></label></div>
        @break
    @case(App\Models\Question::TYPE_MULTIPLE_CHOICE)
        <div class="passenger-answer-options">@forelse ($options as $option)<label><input type="radio" disabled><span>{{ $option }}</span></label>@empty<label><input type="radio" disabled><span>Opsi jawaban</span></label>@endforelse</div>
        @break
    @case(App\Models\Question::TYPE_CHECKBOX)
        <div class="passenger-answer-options">@forelse ($options as $option)<label><input type="checkbox" disabled><span>{{ $option }}</span></label>@empty<label><input type="checkbox" disabled><span>Opsi jawaban</span></label>@endforelse</div>
        @break
    @case(App\Models\Question::TYPE_SHORT_TEXT)
        <input type="text" placeholder="{{ $placeholder ?: 'Tulis jawaban Anda...' }}" disabled>
        @break
    @case(App\Models\Question::TYPE_PARAGRAPH)
        <textarea rows="4" placeholder="{{ $placeholder ?: 'Tulis jawaban Anda...' }}" disabled></textarea>
        @break
@endswitch
