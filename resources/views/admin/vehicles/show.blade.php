<x-layouts.admin title="Detail Kendaraan">
    <div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.vehicles.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>

    <div class="detail-layout branch-detail-layout">
        <x-admin.panel title="Data Kendaraan">
            <div class="detail-grid">
                <x-admin.detail-row label="Cabang" :value="$vehicle->branch?->name" />
                <x-admin.detail-row label="Merk/Model" :value="trim($vehicle->brand . ' ' . $vehicle->model)" />
                <x-admin.detail-row label="Status" :value="$vehicle->status === 'active' ? 'Aktif' : 'Nonaktif'" />
                <x-admin.detail-row label="Tahun" :value="$vehicle->year" />
                <x-admin.detail-row label="Warna" :value="$vehicle->color" />
                <x-admin.detail-row label="Nomor Rangka" :value="$vehicle->chassis_number" />
                <x-admin.detail-row label="Nomor Mesin" :value="$vehicle->engine_number" />
                <x-admin.detail-row label="Bahan Bakar" :value="$vehicle->fuel_type" />
                <x-admin.detail-row label="Transmisi" :value="$vehicle->transmission" />
                <x-admin.detail-row label="Kapasitas" :value="$vehicle->passenger_capacity" />
                <x-admin.detail-row label="Tanggal Perolehan" :value="$vehicle->acquisition_date?->format('d M Y')" />
                <x-admin.detail-row label="Jenis Kepemilikan" :value="$vehicle->ownership_type" />
                <x-admin.detail-row label="Keterangan" :value="$vehicle->description" />
            </div>
        </x-admin.panel>

        <x-admin.panel title="QR Kendaraan">
            <div class="qr-preview-card">
                <img src="{{ app(App\Services\VehicleQrCodeService::class)->dataUri($vehicle) }}" alt="QR {{ $vehicle->police_number }}">
                <strong>{{ $vehicle->police_number }}</strong>
                <span>{{ app(App\Services\VehicleQrCodeService::class)->vehicleUrl($vehicle) }}</span>
            </div>

            <div class="qr-token-box"><span>QR Token</span><code>{{ $vehicle->qr_token }}</code></div>
            <div class="summary-grid"><div><strong>{{ $vehicle->ratings_count }}</strong><span>Total Rating</span></div></div>

            <div class="form-actions stack-actions">
                <a class="primary-button" href="{{ route('admin.vehicles.qr.preview', $vehicle) }}">Preview QR</a>
                <a class="secondary-button" href="{{ route('admin.vehicles.qr.download', $vehicle) }}">Download QR</a>
                <a class="secondary-button" href="{{ route('admin.vehicles.qr.print', $vehicle) }}" target="_blank" rel="noopener">Print QR</a>
                <form method="POST" action="{{ route('admin.vehicles.regenerate-qr', $vehicle) }}">@csrf @method('PATCH')<button class="secondary-button" type="submit">Regenerate QR</button></form>
                <a class="secondary-button" href="{{ route('admin.vehicles.edit', ['vehicle' => $vehicle, 'return_to' => 'detail']) }}">Edit Kendaraan</a>
                <form method="POST" action="{{ route('admin.vehicles.toggle-status', $vehicle) }}">@csrf @method('PATCH')<button class="secondary-button" type="submit">{{ $vehicle->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}">@csrf @method('DELETE')<button class="danger-button" type="submit">Hapus</button></form>
            </div>
        </x-admin.panel>
    </div>
</x-layouts.admin>
