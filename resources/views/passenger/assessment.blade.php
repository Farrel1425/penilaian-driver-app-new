<x-passenger.layout title="Penilaian" variant="assessment">
    <section class="passenger-assessment-hero" aria-labelledby="assessment-page-title">
        <a class="passenger-assessment-back" href="{{ route('passenger.rating.assessor', [$vehicle->qr_token, $driver]) }}" aria-label="Kembali ke data penumpang"><x-lucide-chevron-left aria-hidden="true" /></a>
        <div><h1 id="assessment-page-title">Penilaian Driver</h1><p>Berikan penilaian sesuai pengalaman Anda.</p></div>
    </section>

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

        <aside class="passenger-assessment-summary">
            <div class="passenger-assessment-summary-photo">
                @if ($driver->photo)
                    <img src="{{ Str::startsWith($driver->photo, ['http://', 'https://', '/']) ? $driver->photo : asset('storage/' . $driver->photo) }}" alt="{{ $driver->full_name }}">
                @else
                    <span>{{ strtoupper(substr($driver->full_name, 0, 1)) }}</span>
                @endif
            </div>
            <div><strong>{{ $driver->full_name }}</strong><small>{{ trim($vehicle->brand . ' ' . $vehicle->model) ?: 'Kendaraan' }} · {{ $vehicle->police_number }}</small></div>
        </aside>
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
