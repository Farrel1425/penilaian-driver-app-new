@csrf
@php
    $backUrl = isset($returnTo) && $returnTo === 'detail' ? route('admin.employees.show', $driver) : route('admin.employees.index');
    $selectedCategoryId = (int) old('employee_category_id', $driver->employee_category_id);
    $selectedCategory = $categories->firstWhere('id', $selectedCategoryId);
@endphp
@if (isset($returnTo))
    <input type="hidden" name="return_to" value="{{ $returnTo }}">
@endif
<div class="form-grid">
    <x-admin.select label="Kategori Pegawai" name="employee_category_id" required data-employee-category>
        <option value="">Pilih kategori pegawai</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" data-requires-sim="{{ $category->requires_sim ? 'true' : 'false' }}" @selected($selectedCategoryId === $category->id)>{{ $category->name }}</option>
        @endforeach
    </x-admin.select>
</div>

<div class="form-section-title">Data Pribadi</div>
<div class="form-grid">
    <x-admin.field label="Nama Lengkap" name="full_name" :value="$driver->full_name" required />
    <x-admin.field label="Nama Panggilan" name="nickname" :value="$driver->nickname" />
    <x-admin.field label="Tempat Lahir" name="birth_place" :value="$driver->birth_place" required />
    <x-admin.field label="Tanggal Lahir" name="birth_date" type="date" :value="optional($driver->birth_date)->format('Y-m-d')" required />
    <x-admin.select label="Jenis Kelamin" name="gender" required>
        <option value="">Pilih jenis kelamin</option>
        <option value="male" @selected(old('gender', $driver->gender) === 'male')>Laki-laki</option>
        <option value="female" @selected(old('gender', $driver->gender) === 'female')>Perempuan</option>
    </x-admin.select>
    <x-admin.textarea label="Alamat Lengkap" name="address" :value="$driver->address" required />
    <x-admin.field label="Nomor HP / WhatsApp" name="phone" :value="$driver->phone" required />
    <x-admin.field label="Email" name="email" type="email" :value="$driver->email" />
    <x-admin.select label="Status Pernikahan" name="marital_status">
        <option value="">Pilih status pernikahan</option>
        @foreach (App\Models\Driver::MARITAL_STATUSES as $value => $label)
            <option value="{{ $value }}" @selected(old('marital_status', $driver->marital_status) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.image-cropper label="Foto Pegawai" name="photo" :value="$driver->photo" />
</div>

<div class="form-section-title">Informasi Pekerjaan</div>
<div class="form-grid employee-work-grid">
    <x-admin.select label="Unit Kerja (Cabang)" name="branch_id" required>
        <option value="">Pilih cabang</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" @selected((int) old('branch_id', $driver->branch_id) === $branch->id)>{{ $branch->name }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.field label="Tanggal Bergabung" name="join_date" type="date" :value="optional($driver->join_date)->format('Y-m-d')" required />
    <x-admin.select label="Status" name="status" required>
        @foreach ([App\Models\Driver::STATUS_ACTIVE => 'Aktif', App\Models\Driver::STATUS_INACTIVE => 'Nonaktif'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $driver->status ?? App\Models\Driver::STATUS_ACTIVE) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.field label="Tanggal Berakhir" name="end_date" type="date" :value="optional($driver->end_date)->format('Y-m-d')" />
</div>

<section class="employee-sim-section" data-employee-sim-section @if (! $selectedCategory?->requires_sim) hidden @endif>
    <div class="form-section-title">Data SIM (Opsional)</div>
    <div class="form-grid">
        <x-admin.field label="Nomor SIM" name="sim_number" :value="$driver->sim_number" />
        <x-admin.select label="Jenis SIM" name="sim_type">
            <option value="">Pilih jenis SIM</option>
            @foreach (App\Models\Driver::SIM_TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('sim_type', $driver->sim_type) === $value)>{{ $label }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.field label="Masa Berlaku SIM" name="sim_expired_at" type="date" :value="optional($driver->sim_expired_at)->format('Y-m-d')" />
        <x-admin.image-cropper label="Foto SIM" name="sim_photo" :value="$driver->sim_photo" :aspect-ratio="1.5" crop-label="3:2" />
    </div>
</section>
<div class="form-actions">
    <a class="secondary-button" href="{{ $backUrl }}">Batal</a>
    <button class="primary-button" type="submit">Simpan</button>
</div>
