@csrf
@php
    $backUrl = isset($returnTo) && $returnTo === 'detail' ? route('admin.drivers.show', $driver) : route('admin.drivers.index');
@endphp
@if (isset($returnTo))
    <input type="hidden" name="return_to" value="{{ $returnTo }}">
@endif
<div class="form-section-title">Data Pribadi</div>
<div class="form-grid">
    <x-admin.select label="Cabang" name="branch_id" required>
        <option value="">Pilih cabang</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" @selected((int) old('branch_id', $driver->branch_id) === $branch->id)>{{ $branch->name }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.field label="Nama Lengkap" name="full_name" :value="$driver->full_name" required />
    <x-admin.field label="Nama Panggilan" name="nickname" :value="$driver->nickname" />
    <x-admin.select label="Status" name="status" required>
        @foreach ([App\Models\Driver::STATUS_ACTIVE => 'Aktif', App\Models\Driver::STATUS_INACTIVE => 'Nonaktif'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $driver->status ?? App\Models\Driver::STATUS_ACTIVE) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.field label="Tanggal Bergabung" name="join_date" type="date" :value="optional($driver->join_date)->format('Y-m-d')" required />
    <x-admin.field label="Tempat Lahir" name="birth_place" :value="$driver->birth_place" />
    <x-admin.field label="Tanggal Lahir" name="birth_date" type="date" :value="optional($driver->birth_date)->format('Y-m-d')" />
    <x-admin.select label="Jenis Kelamin" name="gender">
        <option value="">Tidak diisi</option>
        <option value="male" @selected(old('gender', $driver->gender) === 'male')>Laki-laki</option>
        <option value="female" @selected(old('gender', $driver->gender) === 'female')>Perempuan</option>
    </x-admin.select>
    <x-admin.field label="Nomor HP" name="phone" :value="$driver->phone" />
    <x-admin.field label="Email" name="email" type="email" :value="$driver->email" />
    <x-admin.image-cropper label="Foto Driver" name="photo" :value="$driver->photo" />
    <x-admin.textarea label="Alamat" name="address" :value="$driver->address" />
</div>

<div class="form-section-title">Data SIM (Opsional)</div>
<div class="form-grid">
    <x-admin.field label="Nomor SIM" name="sim_number" :value="$driver->sim_number" />
    <x-admin.field label="Jenis SIM" name="sim_type" :value="$driver->sim_type" />
    <x-admin.field label="Masa Berlaku SIM" name="sim_expired_at" type="date" :value="optional($driver->sim_expired_at)->format('Y-m-d')" />
    <x-admin.field label="Path Foto SIM" name="sim_photo" :value="$driver->sim_photo" />
</div>
<div class="form-actions">
    <a class="secondary-button" href="{{ $backUrl }}">Batal</a>
    <button class="primary-button" type="submit">Simpan</button>
</div>
