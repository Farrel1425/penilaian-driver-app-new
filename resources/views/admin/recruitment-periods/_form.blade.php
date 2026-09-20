@csrf
<div class="form-grid">
    <x-admin.field label="Nama Gelombang" name="name" :value="$period->name" placeholder="Contoh: Gelombang Recruitment Oktober 2026" required />
</div>
<div class="form-actions">
    <a class="secondary-button" href="{{ route('admin.recruitment-periods.index') }}">Batal</a>
    <button class="primary-button" type="submit"><x-lucide-check aria-hidden="true" /><span>{{ $period->exists ? 'Simpan Perubahan' : 'Buat Gelombang' }}</span></button>
</div>
