@csrf
@php
    $backUrl = isset($returnTo) && $returnTo === 'detail' ? route('admin.branches.show', $branch) : route('admin.branches.index');
@endphp
@if (isset($returnTo))
    <input type="hidden" name="return_to" value="{{ $returnTo }}">
@endif
<div class="form-grid">
    <x-admin.field label="Kode Unit Kerja" name="code" :value="$branch->code" required />
    <x-admin.field label="Cabang Unit Kerja" name="name" :value="$branch->name" required />
    <x-admin.textarea label="Alamat" name="address" :value="$branch->address" required />
    <x-admin.select label="Kabupaten / Kota" name="regency" required>
        <option value="">Pilih kabupaten / kota</option>
        @foreach (App\Models\Branch::BALI_REGENCIES as $regency)
            <option value="{{ $regency }}" @selected(old('regency', $branch->regency) === $regency)>{{ $regency }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.field label="PIC Unit" name="pic_name" :value="$branch->pic_name" required />
    <x-admin.field label="No. Telepon" name="phone" type="tel" :value="$branch->phone" required />
    <x-admin.field label="Email" name="email" type="email" :value="$branch->email" required />
    <x-admin.select label="Status" name="status" :value="$branch->status ?? App\Models\Branch::STATUS_ACTIVE" required>
        @foreach ([App\Models\Branch::STATUS_ACTIVE => 'Aktif', App\Models\Branch::STATUS_INACTIVE => 'Nonaktif'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $branch->status ?? App\Models\Branch::STATUS_ACTIVE) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
</div>
<div class="form-actions">
    <a class="secondary-button" href="{{ $backUrl }}">Batal</a>
    <button class="primary-button" type="submit">Simpan</button>
</div>
