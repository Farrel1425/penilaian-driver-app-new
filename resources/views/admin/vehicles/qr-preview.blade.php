<x-layouts.admin title="Preview QR Kendaraan">
    <section class="vehicle-qr-preview-page">
        <x-admin.panel>
            <div class="qr-preview-page-toolbar">
                <div>
                    <span class="qr-preview-eyebrow">QR Kendaraan</span>
                    <h2>{{ $vehicle->police_number }}</h2>
                    <p>{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                </div>
                <a class="secondary-button" href="{{ route('admin.vehicles.show', $vehicle) }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a>
            </div>

            <div class="vehicle-qr-preview-layout">
                <div class="qr-preview-card qr-preview-card-large">
                    <img src="{{ $qrDataUri }}" alt="QR {{ $vehicle->police_number }}">
                    <strong>{{ $vehicle->police_number }}</strong>
                    <span>{{ $vehicle->brand }} {{ $vehicle->model }}</span>
                    <small>Scan untuk membuka penilaian kendaraan</small>
                </div>

                <div class="qr-vehicle-information">
                    <div class="qr-info-heading"><x-lucide-car-front aria-hidden="true" /><div><span>Informasi Kendaraan</span><strong>{{ $vehicle->police_number }}</strong></div></div>
                    <dl class="qr-info-list">
                        <div><dt><x-lucide-building-2 aria-hidden="true" /> Unit Kerja / Cabang</dt><dd>{{ $vehicle->branch?->name ?? '-' }}</dd></div>
                        <div><dt><x-lucide-circle-check aria-hidden="true" /> Status Kendaraan</dt><dd><span class="status-badge {{ $vehicle->status === 'active' ? 'is-active' : 'is-inactive' }}">{{ $vehicle->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></dd></div>
                    </dl>
                    <form class="qr-print-settings" method="GET" action="{{ route('admin.vehicles.qr.print', $vehicle) }}" target="_blank">
                        <label for="qr-print-format">Format cetak</label>
                        <select id="qr-print-format" name="format"><option value="a4">A4</option><option value="label">Label QR kecil</option></select>
                        <button class="primary-button" type="submit"><x-lucide-printer aria-hidden="true" /><span>Atur & Cetak QR</span></button>
                    </form>
                </div>
            </div>
        </x-admin.panel>
    </section>
</x-layouts.admin>