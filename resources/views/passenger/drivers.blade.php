<x-passenger.layout title="Pilih Driver" variant="driver-list">
    <header class="passenger-mobile-header">
        <a href="{{ route('passenger.rating.vehicle', $vehicle->qr_token) }}" aria-label="Kembali"><x-lucide-chevron-left aria-hidden="true" /></a>
        <h1>Pilih Driver</h1>
        <p>Pilih driver yang bertugas melayani Anda saat ini.</p>
    </header>

    <section class="passenger-driver-page">
        <div class="passenger-driver-list">
            @forelse($drivers as $driver)
                <a class="passenger-driver-option" href="{{ route('passenger.rating.driver', [$vehicle->qr_token, $driver]) }}">
                    <div class="passenger-driver-photo">
                        @if ($driver->photo)
                            <img src="{{ Str::startsWith($driver->photo, ['http://', 'https://', '/']) ? $driver->photo : asset('storage/' . $driver->photo) }}" alt="{{ $driver->full_name }}">
                        @else
                            <span>{{ strtoupper(substr($driver->full_name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="passenger-driver-summary">
                        <h2>{{ $driver->full_name }}</h2>
                        <small>{{ $driver->branch?->name ?: 'Driver aktif' }}</small>
                        @if ($driver->passenger_average_rating !== null)
                            <p><x-lucide-star aria-hidden="true" /> {{ number_format($driver->passenger_average_rating, 1) }} <span>Penilaian</span></p>
                        @else
                            <p class="passenger-driver-empty-rating">Belum ada rating</p>
                        @endif
                    </div>
                    <span class="passenger-driver-select-label">Pilih <x-lucide-chevron-right aria-hidden="true" /></span>
                </a>
            @empty
                <div class="passenger-driver-empty">Belum ada driver aktif pada cabang kendaraan ini.</div>
            @endforelse
        </div>

        <aside class="passenger-driver-help"><x-lucide-info aria-hidden="true" /><p>Pastikan driver yang dipilih sesuai dengan perjalanan Anda.</p></aside>
    </section>
</x-passenger.layout>
