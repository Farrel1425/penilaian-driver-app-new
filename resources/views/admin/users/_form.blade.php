@php($editing = $user->exists)
<form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($editing) @method('PUT') @endif
    @if ($editing && isset($returnTo)) <input type="hidden" name="return_to" value="{{ $returnTo }}"> @endif

    <x-admin.panel title="Informasi Pengguna" description="Atur akses admin utama atau admin yang hanya membaca laporan untuk satu unit kerja.">
        <div class="form-section-title">Profil Pengguna</div>
        <div class="form-grid">
            <x-admin.image-cropper class="form-field-full" label="Foto Profil" name="photo" :value="$user->photo" />
            <x-admin.field label="Nama Lengkap" name="name" :value="$user->name" required autocomplete="name" />
            <x-admin.field label="Email" name="email" type="email" :value="$user->email" required autocomplete="email" />
            <label class="form-field"><span>Role <b>*</b></span><select name="role" data-user-role><option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected(old('role', $user->role ?: \App\Models\User::ROLE_ADMIN) === \App\Models\User::ROLE_ADMIN)>Admin Utama - akses penuh</option><option value="{{ \App\Models\User::ROLE_BRANCH_ADMIN }}" @selected(old('role', $user->role) === \App\Models\User::ROLE_BRANCH_ADMIN)>Admin Cabang - laporan baca saja</option></select></label>
            <label class="form-field" data-user-branch-field @if (old('role', $user->role ?: \App\Models\User::ROLE_ADMIN) !== \App\Models\User::ROLE_BRANCH_ADMIN) hidden @endif><span>Unit Kerja <b>*</b></span><select name="branch_id" data-user-branch-select><option value="">Pilih Unit Kerja</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}" @selected((string) old('branch_id', $user->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>@endforeach</select></label>
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
            <button type="submit" class="primary-button">{{ $editing ? 'Simpan Perubahan' : 'Simpan Pengguna' }}</button>
        </div>
    </x-admin.panel>
</form>
<script>
    (() => {
        const role = document.querySelector('[data-user-role]');
        const branch = document.querySelector('[data-user-branch-field]');
        const branchSelect = document.querySelector('[data-user-branch-select]');
        const sync = () => {
            const isBranchAdmin = role.value === '{{ \App\Models\User::ROLE_BRANCH_ADMIN }}';
            branch.hidden = ! isBranchAdmin;
            if (! isBranchAdmin) branchSelect.value = '';
        };
        role.addEventListener('change', sync);
        sync();
    })();
</script>