@php($editing = $user->exists)
<form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($editing) @method('PUT') @endif
    @if ($editing && isset($returnTo)) <input type="hidden" name="return_to" value="{{ $returnTo }}"> @endif

    <x-admin.panel title="Informasi Admin" description="Data akun administrator dengan akses penuh ke seluruh sistem.">
        <div class="form-section-title">Profil Administrator</div>
        <div class="form-grid">
            <x-admin.image-cropper class="form-field-full" label="Foto Profil" name="photo" :value="$user->photo" />
            <x-admin.field label="Nama Lengkap" name="name" :value="$user->name" required autocomplete="name" />
            <x-admin.field label="Email" name="email" type="email" :value="$user->email" required autocomplete="email" />
            <label class="form-field"><span>Role</span><input value="Admin - akses penuh" readonly></label>
            <x-admin.select label="Status Akun" name="status" required>
                <option value="active" @selected(old('status', $user->status ?: 'active') === 'active')>Aktif</option>
                <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Nonaktif</option>
            </x-admin.select>
        </div>

        <div class="form-section-title">Keamanan Akses</div>
        <div class="form-grid">
            <x-admin.field label="Kata Sandi" name="password" type="password" :required="! $editing" autocomplete="new-password" />
            <x-admin.field label="Konfirmasi Kata Sandi" name="password_confirmation" type="password" :required="! $editing" autocomplete="new-password" />
        </div>

        <div class="form-actions">
            <a href="{{ $editing && ($returnTo ?? 'index') === 'detail' ? route('admin.users.show', $user) : route('admin.users.index') }}" class="secondary-button">Batal</a>
            <button type="submit" class="primary-button">{{ $editing ? 'Simpan Perubahan' : 'Simpan Admin' }}</button>
        </div>
    </x-admin.panel>
</form>
