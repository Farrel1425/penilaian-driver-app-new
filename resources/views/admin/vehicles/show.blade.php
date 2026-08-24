<x-layouts.admin title="Detail Kendaraan">
    @php
        $exteriorPhotoUrl = $vehicle->photo ? (Str::startsWith($vehicle->photo, ['http://', 'https://', '/']) ? $vehicle->photo : asset('storage/'.$vehicle->photo)) : null;
        $interiorPhotoUrl = $vehicle->interior_photo ? (Str::startsWith($vehicle->interior_photo, ['http://', 'https://', '/']) ? $vehicle->interior_photo : asset('storage/'.$vehicle->interior_photo)) : null;
        $qrDataUri = app(App\Services\VehicleQrCodeService::class)->dataUri($vehicle);
    @endphp

    <div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.vehicles.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>

    <div class="detail-layout branch-detail-layout">
        <x-admin.panel title="Data Kendaraan">
            <div class="vehicle-detail-view">
                <div class="vehicle-detail-photos">
                    <figure><figcaption>Foto Eksterior</figcaption>@if ($exteriorPhotoUrl)<img src="{{ $exteriorPhotoUrl }}" alt="Foto eksterior {{ $vehicle->police_number }}">@else<div><x-lucide-car-front aria-hidden="true" /><small>Belum ada foto</small></div>@endif</figure>
                    <figure><figcaption>Foto Interior</figcaption>@if ($interiorPhotoUrl)<img src="{{ $interiorPhotoUrl }}" alt="Foto interior {{ $vehicle->police_number }}">@else<div><x-lucide-image aria-hidden="true" /><small>Belum ada foto</small></div>@endif</figure>
                </div>
                <dl class="vehicle-detail-list">
                    <div><dt>No. Polisi</dt><dd>{{ $vehicle->police_number }}</dd></div><div><dt>Merk / Model</dt><dd>{{ trim($vehicle->brand.' '.$vehicle->model) }}</dd></div><div><dt>Tahun</dt><dd>{{ $vehicle->year }}</dd></div><div><dt>Warna</dt><dd>{{ $vehicle->color }}</dd></div><div><dt>No. Rangka / VIN</dt><dd>{{ $vehicle->chassis_number }}</dd></div><div><dt>No. Mesin</dt><dd>{{ $vehicle->engine_number }}</dd></div><div><dt>Bahan Bakar</dt><dd>{{ App\Models\Vehicle::FUEL_TYPES[$vehicle->fuel_type] ?? null }}</dd></div><div><dt>Transmisi</dt><dd>{{ App\Models\Vehicle::TRANSMISSION_TYPES[$vehicle->transmission] ?? null }}</dd></div><div><dt>Kapasitas Penumpang</dt><dd>{{ $vehicle->passenger_capacity }}</dd></div>
                </dl>
                <dl class="vehicle-detail-list">
                    <div><dt>Unit Kerja</dt><dd>{{ $vehicle->branch?->name }}</dd></div><div><dt>Status</dt><dd>{{ $vehicle->status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd></div><div><dt>Tanggal Pengadaan</dt><dd>{{ $vehicle->acquisition_date?->format('d/m/Y') }}</dd></div><div><dt>Sumber Pengadaan</dt><dd>{{ App\Models\Vehicle::ACQUISITION_SOURCES[$vehicle->acquisition_source] ?? null }}</dd></div><div><dt>Kepemilikan</dt><dd>{{ App\Models\Vehicle::OWNERSHIP_TYPES[$vehicle->ownership_type] ?? null }}</dd></div><div><dt>No. Kontrak</dt><dd>{{ $vehicle->contract_number }}</dd></div><div><dt>Masa Berlaku Kontrak</dt><dd>{{ $vehicle->contract_expired_at?->format('d/m/Y') }}</dd></div><div><dt>Masa Berlaku STNK</dt><dd>{{ $vehicle->stnk_expired_at?->format('d/m/Y') }}</dd></div><div><dt>Masa Berlaku KIR</dt><dd>{{ $vehicle->kir_expired_at?->format('d/m/Y') }}</dd></div><div><dt>Keterangan</dt><dd>{{ $vehicle->description }}</dd></div>
                </dl>
            </div>
        </x-admin.panel>

        <div class="vehicle-detail-side">
            <x-admin.panel title="QR Kendaraan">
                <div class="vehicle-qr-detail-card">
                    <details class="vehicle-qr-menu">
                        <summary aria-label="Aksi QR kendaraan" title="Aksi QR"><x-lucide-download aria-hidden="true" /></summary>
                        <div class="vehicle-qr-menu-content">
                            <a href="{{ route('admin.vehicles.qr.preview', $vehicle) }}"><x-lucide-expand aria-hidden="true" /><span>Preview QR</span></a>
                            <a href="{{ route('admin.vehicles.qr.download', $vehicle) }}"><x-lucide-download aria-hidden="true" /><span>Download QR</span></a>
                            <a href="{{ route('admin.vehicles.qr.print', $vehicle) }}" target="_blank" rel="noopener"><x-lucide-printer aria-hidden="true" /><span>Atur & Cetak QR</span></a>
                            <form method="POST" action="{{ route('admin.vehicles.regenerate-qr', $vehicle) }}">@csrf @method('PATCH')<button type="submit"><x-lucide-refresh-cw aria-hidden="true" /><span>Regenerate QR</span></button></form>
                        </div>
                    </details>
                    <button class="vehicle-detail-qr-open" type="button" data-vehicle-qr-trigger data-qr-src="{{ $qrDataUri }}" data-qr-title="{{ $vehicle->police_number }}" data-qr-description="{{ trim($vehicle->brand.' '.$vehicle->model) }} - {{ $vehicle->branch?->name }}" data-qr-download="{{ route('admin.vehicles.qr.download', $vehicle) }}" aria-label="Buka QR {{ $vehicle->police_number }}">
                        <img src="{{ $qrDataUri }}" alt="QR {{ $vehicle->police_number }}"><strong>{{ $vehicle->police_number }}</strong><span>Ketuk untuk memperbesar QR</span>
                    </button>
                    <div class="vehicle-qr-rating"><strong>{{ $vehicle->ratings_count }}</strong><span>Total Rating</span></div>
                </div>
            </x-admin.panel>

            <x-admin.panel title="Aksi Kendaraan">
                <div class="vehicle-record-actions">
                    <a class="primary-button" href="{{ route('admin.vehicles.edit', ['vehicle' => $vehicle, 'return_to' => 'detail']) }}"><x-lucide-pencil aria-hidden="true" /><span>Edit Kendaraan</span></a>
                    <form method="POST" action="{{ route('admin.vehicles.toggle-status', $vehicle) }}" data-confirm data-no-loading data-confirm-title="{{ $vehicle->status === 'active' ? 'Nonaktifkan kendaraan?' : 'Aktifkan kendaraan?' }}" data-confirm-description="{{ $vehicle->status === 'active' ? 'Kendaraan '.$vehicle->police_number.' tidak dapat digunakan untuk penilaian sampai diaktifkan kembali.' : 'Kendaraan '.$vehicle->police_number.' dapat digunakan kembali untuk penilaian.' }}" data-confirm-label="{{ $vehicle->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}" data-confirm-tone="primary" data-confirm-icon="power">@csrf @method('PATCH')<button @class(['status-toggle-action', 'is-active' => $vehicle->status === 'active']) type="submit"><x-lucide-power aria-hidden="true" /><span>{{ $vehicle->status === 'active' ? 'Nonaktifkan Kendaraan' : 'Aktifkan Kendaraan' }}</span></button></form>
                    <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}" data-delete-confirm data-no-loading data-delete-name="Kendaraan {{ $vehicle->police_number }}" data-delete-description="Kendaraan yang sudah memiliki penilaian akan dinonaktifkan agar riwayat penilaian tetap tersimpan.">@csrf @method('DELETE')<button class="danger-button" type="submit"><x-lucide-trash-2 aria-hidden="true" /><span>Hapus Kendaraan</span></button></form>
                </div>
            </x-admin.panel>
        </div>
    </div>
</x-layouts.admin>
