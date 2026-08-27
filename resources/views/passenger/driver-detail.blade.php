<x-passenger.layout title="Detail Driver" variant="driver-detail">
    <header class="passenger-mobile-header">
        <a href="{{ route('passenger.rating.drivers', $vehicle->qr_token) }}" aria-label="Kembali ke pilihan driver"><x-lucide-chevron-left aria-hidden="true" /></a>
        <h1>Detail Driver</h1>
    </header>

    <section class="passenger-driver-detail-page">
        <div class="passenger-driver-profile">
            <div class="passenger-driver-detail-photo">
                @if ($driver->photo)
                    <img src="{{ Str::startsWith($driver->photo, ['http://', 'https://', '/']) ? $driver->photo : asset('storage/' . $driver->photo) }}" alt="{{ $driver->full_name }}">
                @else
                    <span>{{ strtoupper(substr($driver->full_name, 0, 1)) }}</span>
                @endif
            </div>
            <h2>{{ $driver->full_name }}</h2>
            <span class="passenger-driver-active"><i aria-hidden="true"></i>Driver Aktif</span>
        </div>

        <dl class="passenger-driver-detail-card">
            <div>
                <span class="passenger-driver-detail-icon"><x-lucide-building-2 aria-hidden="true" /></span>
                <div><dt>Unit Kerja</dt><dd>{{ $driver->branch?->name ?: '-' }}</dd></div>
            </div>
            <div>
                <span class="passenger-driver-detail-icon"><x-lucide-contact-round aria-hidden="true" /></span>
                <div><dt>No. SIM</dt><dd>{{ trim(($driver->sim_type ? $driver->sim_type . ' - ' : '') . ($driver->sim_number ?: '-')) }}</dd></div>
            </div>
            <div>
                <span class="passenger-driver-detail-icon"><x-lucide-calendar-check-2 aria-hidden="true" /></span>
                <div><dt>Berlaku Hingga</dt><dd>{{ $driver->sim_expired_at?->format('d/m/Y') ?: '-' }}</dd></div>
            </div>
        </dl>
    </section>

    <footer class="passenger-driver-detail-footer">
        <a class="passenger-driver-select" href="{{ route('passenger.rating.assessor', [$vehicle->qr_token, $driver]) }}">Pilih Driver Ini</a>
        <a class="passenger-driver-other" href="{{ route('passenger.rating.drivers', $vehicle->qr_token) }}">Pilih Driver Lain</a>
    </footer>
</x-passenger.layout>
