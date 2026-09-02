<x-passenger.layout title="Penilaian" variant="assessment">
    <header class="passenger-mobile-header passenger-scroll-header">
        <a href="{{ route('passenger.rating.assessor', [$vehicle->qr_token, $driver]) }}" aria-label="Kembali ke isi nama"><x-lucide-chevron-left aria-hidden="true" /></a>
        <h1>Penilaian</h1>
    </header>

    @php
        $allQuestions = $questions->get(App\Models\Question::TARGET_DRIVER, collect())
            ->concat($questions->get(App\Models\Question::TARGET_VEHICLE, collect()))
            ->concat($questions->get(App\Models\Question::TARGET_FEEDBACK, collect()))
            ->values();
    @endphp

    <form class="passenger-assessment-page passenger-assessment-scroll" method="POST" action="{{ route('passenger.rating.submit', [$vehicle->qr_token, $driver]) }}">
        @csrf
        <input type="hidden" name="passenger_name" value="{{ $passengerName }}">
        <input type="hidden" name="passenger_unit" value="{{ $passengerUnit }}">
        <input type="hidden" name="submission_token" value="{{ $submissionToken }}">

        <section class="passenger-assessment-scroll-list">
            @forelse ($allQuestions as $question)
                @include('passenger.partials.question', ['question' => $question, 'number' => $loop->iteration, 'scroll' => true])
            @empty
                <div class="passenger-assessment-empty">Belum ada pertanyaan penilaian aktif.</div>
            @endforelse
        </section>

        <footer class="passenger-assessment-footer passenger-scroll-footer">
            <button type="submit" data-submitting-label="Mengirim Penilaian...">Kirim Penilaian <x-lucide-send aria-hidden="true" /></button>
        </footer>
    </form>

    <script>
        const feedbackFollowUp = document.querySelector('[data-feedback-followup]');
        const syncFeedbackFollowUp = (input) => {
            if (feedbackFollowUp && input) {
                feedbackFollowUp.hidden = input.dataset.feedbackEmpty === 'true';
            }
        };

        syncFeedbackFollowUp(document.querySelector('[data-feedback-choice]:checked'));
        document.querySelectorAll('[data-feedback-choice]').forEach((input) => {
            input.addEventListener('change', () => syncFeedbackFollowUp(input));
        });
    </script>
</x-passenger.layout>