<x-passenger.layout title="Informasi Kendaraan" variant="vehicle">
    <header class="passenger-mobile-header">
        {{-- <button type="button" aria-label="Kembali" onclick="window.history.back()"><x-lucide-chevron-left aria-hidden="true" /></button> --}}
        <h1>Informasi Kendaraan</h1>
    </header>

    <section class="passenger-vehicle-page">
        <article class="passenger-vehicle-card">
            <div class="passenger-vehicle-heading">
                <h2>{{ $vehicle->police_number }}</h2>
                <p>{{ trim($vehicle->brand . ' ' . $vehicle->model) ?: '-' }}</p>
            </div>

            <div class="passenger-vehicle-photo">
                @if ($vehicle->photo)
                    <img src="{{ Str::startsWith($vehicle->photo, ['http://', 'https://', '/']) ? $vehicle->photo : asset('storage/' . $vehicle->photo) }}" alt="{{ $vehicle->police_number }}">
                @else
                    <x-lucide-car-front aria-hidden="true" />
                @endif
            </div>

            <dl class="passenger-vehicle-details">
                <div>
                    <x-lucide-building-2 aria-hidden="true" />
                    <div><dt>Unit Kerja / Cabang</dt><dd>{{ $vehicle->branch?->name ?: '-' }}</dd></div>
                </div>
                <div>
                    <x-lucide-panel-top-dashed aria-hidden="true" />
                    <div><dt>No. Polisi</dt><dd>{{ $vehicle->police_number }}</dd></div>
                </div>
                <div>
                    <x-lucide-car-front aria-hidden="true" />
                    <div><dt>Merk / Tipe</dt><dd>{{ trim($vehicle->brand . ' ' . $vehicle->model) ?: '-' }}</dd></div>
                </div>
            </dl>
        </article>
    </section>

    <footer class="passenger-vehicle-footer">
        <a class="passenger-vehicle-continue" href="{{ route('passenger.rating.drivers', $vehicle->qr_token) }}">Lanjutkan</a>
        <button class="passenger-vehicle-rescan" type="button" data-passenger-qr-scanner-open>
            <x-lucide-scan-line aria-hidden="true" />
            <span>Scan Ulang QR</span>
        </button>
    </footer>

    <div class="passenger-qr-scanner" data-passenger-qr-scanner hidden>
        <button class="passenger-qr-scanner-backdrop" type="button" data-passenger-qr-scanner-close aria-label="Tutup pemindai QR"></button>
        <section class="passenger-qr-scanner-dialog" role="dialog" aria-modal="true" aria-labelledby="passenger-qr-scanner-title">
            <header>
                <div>
                    <span>SCAN QR</span>
                    <h2 id="passenger-qr-scanner-title">Arahkan kamera ke QR kendaraan</h2>
                </div>
                <button type="button" data-passenger-qr-scanner-close aria-label="Tutup pemindai QR"><x-lucide-x aria-hidden="true" /></button>
            </header>
            <div class="passenger-qr-scanner-viewport">
                <video data-passenger-qr-scanner-video autoplay muted playsinline></video>
                <span class="passenger-qr-scanner-frame" aria-hidden="true"></span>
            </div>
            <p data-passenger-qr-scanner-status>Menyiapkan kamera...</p>
            <button class="passenger-qr-scanner-retry" type="button" data-passenger-qr-scanner-retry hidden>
                <x-lucide-refresh-cw aria-hidden="true" />
                <span>Coba Lagi</span>
            </button>
        </section>
    </div>
</x-passenger.layout>
