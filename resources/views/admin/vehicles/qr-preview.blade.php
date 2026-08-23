<x-layouts.admin title="Preview QR Kendaraan">
    <x-admin.page-header title="Preview QR" description="QR ini mengarah ke halaman awal penilaian kendaraan.">
        <a class="secondary-button" href="{{ route('admin.vehicles.show', $vehicle) }}">Kembali</a>
    </x-admin.page-header>

    <x-admin.panel>
        <div class="qr-page-preview">
            <div class="qr-preview-card qr-preview-card-large">
                <img src="{{ $qrDataUri }}" alt="QR {{ $vehicle->police_number }}">
                <strong>{{ $vehicle->police_number }}</strong>
                <span>{{ $vehicle->brand }} {{ $vehicle->model }}</span>
            </div>
            <div class="detail-grid">
                <x-admin.detail-row label="Cabang" :value="$vehicle->branch?->name" />
                <x-admin.detail-row label="Status Kendaraan" :value="$vehicle->status === 'active' ? 'Aktif' : 'Nonaktif'" />
            </div>
            <form class="qr-print-settings" method="GET" action="{{ route('admin.vehicles.qr.print', $vehicle) }}" target="_blank">
                <label for="qr-print-format">Atur cetak</label>
                <select id="qr-print-format" name="format">
                    <option value="a4">A4</option>
                    <option value="label">Label QR kecil</option>
                </select>
                <button class="primary-button" type="submit"><x-lucide-printer aria-hidden="true" /><span>Buka preview cetak</span></button>
            </form>
        </div>
    </x-admin.panel>
</x-layouts.admin>
