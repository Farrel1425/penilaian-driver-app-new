@csrf
<div class="form-grid">
    <x-admin.field label="Nama Kategori" name="name" :value="$category->name" required />
    <x-admin.select label="Status" name="status" required>
        @foreach ([App\Models\EmployeeCategory::STATUS_ACTIVE => 'Aktif', App\Models\EmployeeCategory::STATUS_INACTIVE => 'Nonaktif'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $category->status ?? App\Models\EmployeeCategory::STATUS_ACTIVE) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
</div>
<label class="employee-category-sim-toggle">
    <input type="hidden" name="requires_sim" value="0">
    <input type="checkbox" name="requires_sim" value="1" @checked(old('requires_sim', $category->requires_sim))>
    <span><strong>Memerlukan data SIM</strong><small>Tampilkan data SIM pada form pegawai dan izinkan pegawai dipilih pada penilaian driver.</small></span>
</label>
<div class="form-actions">
    <a class="secondary-button" href="{{ route('admin.employee-categories.index') }}">Batal</a>
    <button class="primary-button" type="submit">Simpan</button>
</div>
