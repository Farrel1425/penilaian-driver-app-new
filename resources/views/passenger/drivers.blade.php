<x-passenger.layout title="Pilih Driver" variant="driver-list">
    <header class="passenger-mobile-header">
        <a href="{{ route('passenger.rating.vehicle', $vehicle->qr_token) }}" aria-label="Kembali"><x-lucide-chevron-left aria-hidden="true" /></a>
        <h1>Pilih Driver</h1>
    </header>

    <section class="passenger-driver-page">
        <p class="passenger-driver-instruction">Pilih driver yang bertugas di unit kerja ini</p>

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
                        @if ($driver->passenger_average_rating !== null)
                            <p>{{ number_format($driver->passenger_average_rating, 1) }} <x-lucide-star aria-hidden="true" /></p>
                        @else
                            <p class="passenger-driver-empty-rating">Belum ada rating</p>
                        @endif
                    </div>
                    <x-lucide-chevron-right class="passenger-driver-arrow" aria-hidden="true" />
                </a>
            @empty
                <div class="passenger-driver-empty">Belum ada driver aktif pada cabang kendaraan ini.</div>
            @endforelse
        </div>

        <aside class="passenger-driver-help">
            <x-lucide-info aria-hidden="true" />
            <p>Sesuaikan dengan driver yang bertugas</p>
        </aside>
    </section>
</x-passenger.layout>
