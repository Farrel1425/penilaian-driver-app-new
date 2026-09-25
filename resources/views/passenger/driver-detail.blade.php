<x-passenger.layout title="Detail Driver" variant="driver-detail">
    <header class="passenger-mobile-header">
        <a href="{{ route('passenger.rating.drivers', $vehicle->qr_token) }}" aria-label="Kembali ke pilihan driver"><x-lucide-chevron-left aria-hidden="true" /></a>
        <h1>Konfirmasi Driver</h1>
        <p>Pastikan driver yang bertugas melayani Anda saat ini.</p>
    </header>

    <section class="passenger-driver-detail-page">
        <div class="passenger-driver-profile">
            <div class="passenger-driver-detail-photo">
                <x-entity-photo type="driver" :src="$driver->photo" :alt="$driver->full_name" />
            </div>
            <h2>{{ $driver->full_name }}</h2>
            <p>Mitra Terverifikasi</p>
            <span class="passenger-driver-active"><i aria-hidden="true"></i>Aktif Sekarang</span>
        </div>

        <dl class="passenger-driver-detail-card">
            <div>
                <span class="passenger-driver-detail-icon"><x-lucide-building-2 aria-hidden="true" /></span>
                <div><dt>Unit Kerja</dt><dd>{{ $driver->branch?->name ?: '-' }}</dd><small>Penugasan saat ini</small></div>
            </div>
            <div>
                <span class="passenger-driver-detail-icon"><x-lucide-contact-round aria-hidden="true" /></span>
                <div><dt>Surat Izin Mengemudi</dt><dd>{{ trim(($driver->sim_type ? $driver->sim_type . ' - ' : '') . ($driver->sim_number ?: '-')) }}</dd></div>
            </div>
            <div>
                <span class="passenger-driver-detail-icon"><x-lucide-calendar-check-2 aria-hidden="true" /></span>
                <div><dt>Masa Berlaku</dt><dd>{{ $driver->sim_expired_at?->format('d M Y') ?: '-' }}</dd></div>
            </div>
        </dl>
    </section>

    <footer class="passenger-driver-detail-footer">
        <a class="passenger-driver-select" href="{{ route('passenger.rating.assessor', [$vehicle->qr_token, $driver]) }}"><x-lucide-user-check aria-hidden="true" /> Pilih Driver Ini</a>
        <a class="passenger-driver-other" href="{{ route('passenger.rating.drivers', $vehicle->qr_token) }}">Pilih Driver Lain</a>
    </footer>
</x-passenger.layout>
