@csrf
<div class="form-grid recruitment-vacancy-form">
    <x-admin.select label="Gelombang Recruitment" name="recruitment_period_id" required>
        <option value="">Pilih gelombang</option>
        @foreach ($periods as $period)<option value="{{ $period->id }}" @selected((string) old('recruitment_period_id', $vacancy->recruitment_period_id) === (string) $period->id)>{{ $period->name }}{{ $period->is_active ? ' (Aktif)' : '' }}</option>@endforeach
    </x-admin.select>
    <x-admin.field label="Kategori Pekerjaan" name="category" :value="$vacancy->category" placeholder="Contoh: Armada & Driver" required />
    <x-admin.field label="Jenis Kontrak / Jadwal" name="work_type" :value="$vacancy->work_type" placeholder="Contoh: Penuh Waktu" required />
    <div class="form-field form-field-full"><label for="title">Nama Posisi <span>*</span></label><input id="title" name="title" value="{{ old('title', $vacancy->title) }}" required maxlength="180">@error('title')<small class="form-error">{{ $message }}</small>@enderror</div>
    <div class="form-field form-field-full"><label for="description">Deskripsi Pekerjaan <span>*</span></label><textarea id="description" name="description" rows="4" required>{{ old('description', $vacancy->description) }}</textarea>@error('description')<small class="form-error">{{ $message }}</small>@enderror</div>
    <div class="form-field form-field-full"><label for="qualification">Kualifikasi <span>*</span></label><textarea id="qualification" name="qualification" rows="3" required placeholder="Contoh: Wajib SIM A / B1 aktif, rekam jejak aman">{{ old('qualification', $vacancy->qualification) }}</textarea>@error('qualification')<small class="form-error">{{ $message }}</small>@enderror</div>
    <div class="form-field form-field-full"><label for="compensation">Gaji dan Fasilitas <span>*</span></label><textarea id="compensation" name="compensation" rows="3" required placeholder="Contoh: Gaji UMK + uang perjalanan + lembur">{{ old('compensation', $vacancy->compensation) }}</textarea>@error('compensation')<small class="form-error">{{ $message }}</small>@enderror</div>
    <x-admin.field label="Jumlah Formasi" name="quota" type="number" min="1" :value="$vacancy->quota" required />
</div>

@php($selectedBranches = collect(old('branch_ids', $vacancy->exists ? $vacancy->branches->pluck('id')->all() : []))->map(fn ($id) => (string) $id))
<fieldset class="recruitment-branch-fieldset">
    <legend>Cabang Penempatan <span>*</span></legend>
    <p>Pilih satu atau beberapa cabang aktif yang tersedia untuk lowongan ini.</p>
    <div class="recruitment-branch-options">
        @foreach ($branches as $branch)
            <label><input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" @checked($selectedBranches->contains((string) $branch->id))><span><strong>{{ $branch->name }}</strong><small>{{ $branch->code }}@if ($branch->regency) &bull; {{ $branch->regency }}@endif</small></span></label>
        @endforeach
    </div>
    @error('branch_ids')<small class="form-error">{{ $message }}</small>@enderror
    @error('branch_ids.*')<small class="form-error">{{ $message }}</small>@enderror
</fieldset>

<div class="form-actions">
    <a class="secondary-button" href="{{ route('admin.job-vacancies.index') }}">Batal</a>
    <button class="primary-button" type="submit"><x-lucide-check aria-hidden="true" /><span>{{ $vacancy->exists ? 'Simpan Perubahan' : 'Buat Lowongan' }}</span></button>
</div>
