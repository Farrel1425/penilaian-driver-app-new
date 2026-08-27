<x-passenger.layout title="Identitas Penilai" variant="assessor">
    <header class="passenger-mobile-header">
        <a href="{{ route('passenger.rating.driver', [$vehicle->qr_token, $driver]) }}" aria-label="Kembali ke detail driver"><x-lucide-chevron-left aria-hidden="true" /></a>
        <h1>Identitas Penilai</h1>
    </header>

    <form class="passenger-assessor-page" method="POST" action="{{ route('passenger.rating.assessor.store', [$vehicle->qr_token, $driver]) }}">
        @csrf
        <section class="passenger-assessor-card">
            <span class="passenger-assessor-icon"><x-lucide-user-round aria-hidden="true" /></span>
            <h2>Siapa nama Anda?</h2>
            <p>Masukkan nama agar penilaian Anda dapat dicatat dengan baik.</p>

            @if (session('error'))
                <p class="passenger-assessor-error" role="alert">{{ session('error') }}</p>
            @endif

            <label for="passenger_name">Nama Anda</label>
            <input id="passenger_name" name="passenger_name" type="text" value="{{ old('passenger_name', $passengerName) }}" placeholder="Contoh: Made Putra" maxlength="100" autocomplete="name" autofocus required>
            @error('passenger_name')<p class="passenger-assessor-error" role="alert">{{ $message }}</p>@enderror
        </section>

        <footer class="passenger-assessor-footer">
            <button type="submit">Lanjut ke Penilaian <x-lucide-arrow-right aria-hidden="true" /></button>
        </footer>
    </form>
</x-passenger.layout>
