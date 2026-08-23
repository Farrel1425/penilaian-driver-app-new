@csrf
@php
    $backUrl = isset($returnTo) && $returnTo === 'detail' ? route('admin.vehicles.show', $vehicle) : route('admin.vehicles.index');
@endphp
@if (isset($returnTo))
    <input type="hidden" name="return_to" value="{{ $returnTo }}">
@endif
<div class="form-section-title">Foto Kendaraan</div>
<div class="vehicle-photo-editor-grid">
    <x-admin.image-cropper label="Foto Eksterior" name="photo" :value="$vehicle->photo" :aspect-ratio="1.333333" crop-label="4:3" variant="vehicle" />
    <x-admin.image-cropper label="Foto Interior" name="interior_photo" :value="$vehicle->interior_photo" :aspect-ratio="1.333333" crop-label="4:3" variant="vehicle" />
</div>
<div class="form-section-title">Informasi Kendaraan</div>
<div class="form-grid">
    <x-admin.select label="Cabang" name="branch_id" required><option value="">Pilih cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('branch_id', $vehicle->branch_id) === $branch->id)>{{ $branch->name }}</option>@endforeach</x-admin.select>
    <x-admin.field label="Nomor Polisi" name="police_number" :value="$vehicle->police_number" required />
    <x-admin.field label="Merk" name="brand" :value="$vehicle->brand" required />
    <x-admin.field label="Model" name="model" :value="$vehicle->model" />
    <x-admin.field label="Tahun" name="year" type="number" :value="$vehicle->year" />
    <x-admin.field label="Warna" name="color" :value="$vehicle->color" />
    <x-admin.field label="Nomor Rangka" name="chassis_number" :value="$vehicle->chassis_number" />
    <x-admin.field label="Nomor Mesin" name="engine_number" :value="$vehicle->engine_number" />
    <x-admin.select label="Bahan Bakar" name="fuel_type">
        <option value="">Pilih bahan bakar</option>
        @foreach (App\Models\Vehicle::FUEL_TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('fuel_type', $vehicle->fuel_type) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select label="Transmisi" name="transmission">
        <option value="">Pilih transmisi</option>
        @foreach (App\Models\Vehicle::TRANSMISSION_TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('transmission', $vehicle->transmission) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.field label="Kapasitas Penumpang" name="passenger_capacity" type="number" :value="$vehicle->passenger_capacity" />
</div>
<div class="form-section-title">Informasi Operasional</div>
<div class="form-grid">
    <x-admin.select label="Status" name="status" required>@foreach([App\Models\Vehicle::STATUS_ACTIVE => 'Aktif', App\Models\Vehicle::STATUS_INACTIVE => 'Nonaktif'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $vehicle->status ?? App\Models\Vehicle::STATUS_ACTIVE) === $value)>{{ $label }}</option>@endforeach</x-admin.select>
    <x-admin.field label="Tanggal Perolehan" name="acquisition_date" type="date" :value="optional($vehicle->acquisition_date)->format('Y-m-d')" />
    <x-admin.select label="Sumber Pengadaan" name="acquisition_source">
        <option value="">Pilih sumber pengadaan</option>
        @foreach (App\Models\Vehicle::ACQUISITION_SOURCES as $value => $label)
            <option value="{{ $value }}" @selected(old('acquisition_source', $vehicle->acquisition_source) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select label="Jenis Kepemilikan" name="ownership_type">
        <option value="">Pilih jenis kepemilikan</option>
        @foreach (App\Models\Vehicle::OWNERSHIP_TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('ownership_type', $vehicle->ownership_type) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.field label="Nomor Kontrak" name="contract_number" :value="$vehicle->contract_number" />
    <x-admin.field label="Masa Berlaku Kontrak" name="contract_expired_at" type="date" :value="optional($vehicle->contract_expired_at)->format('Y-m-d')" />
    <x-admin.field label="Masa Berlaku STNK" name="stnk_expired_at" type="date" :value="optional($vehicle->stnk_expired_at)->format('Y-m-d')" />
    <x-admin.field label="Masa Berlaku KIR" name="kir_expired_at" type="date" :value="optional($vehicle->kir_expired_at)->format('Y-m-d')" />
    <x-admin.textarea label="Keterangan" name="description" :value="$vehicle->description" />
</div>
<div class="form-actions"><a class="secondary-button" href="{{ $backUrl }}">Batal</a><button class="primary-button" type="submit">Simpan</button></div>
