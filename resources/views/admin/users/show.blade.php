<x-layouts.admin title="Detail Pengguna">
    <x-slot:pageActions><div class="resource-page-navigation">
        <a class="primary-button" href="{{ route('admin.users.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a>
    </div></x-slot>

    <div class="detail-layout admin-user-detail-layout">
        <x-admin.panel title="Informasi Pengguna">
            <div class="admin-user-identity">
                <span class="user-photo user-photo-xl">@if ($user->photo)<img src="{{ str_starts_with($user->photo, 'http') ? $user->photo : asset('storage/'.$user->photo) }}" alt="Foto {{ $user->name }}">@else{{ str($user->name)->substr(0, 1)->upper() }}@endif</span>
                <div><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p><span class="user-role-badge">{{ $user->isBranchAdmin() ? 'Admin Cabang - laporan baca saja' : 'Admin Utama - akses penuh' }}</span></div>
            </div>
            <div class="detail-grid admin-user-detail-grid">
                <x-admin.detail-row label="Nama Lengkap" :value="$user->name" />
                <x-admin.detail-row label="Email" :value="$user->email" />
                <x-admin.detail-row label="Role" :value="$user->isBranchAdmin() ? 'Admin Cabang' : 'Admin Utama'" />
                <x-admin.detail-row label="Unit Kerja" :value="$user->isBranchAdmin() ? ($user->branch?->name ?? '-') : 'Semua unit kerja'" />
                <x-admin.detail-row label="Status Akun" :value="$user->status === \App\Models\User::STATUS_ACTIVE ? 'Aktif' : 'Nonaktif'" />
                <x-admin.detail-row label="Dibuat Pada" :value="$user->created_at->translatedFormat('d F Y, H:i')" />
            </div>
        </x-admin.panel>

        <x-admin.panel title="Aksi Pengguna">
            <div class="admin-user-action-list record-actions">
                <a class="primary-button admin-user-edit-action" href="{{ route('admin.users.edit', ['user' => $user, 'return_to' => 'detail']) }}"><x-lucide-pencil aria-hidden="true" /><span>Edit Pengguna</span></a>
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" data-confirm data-no-loading data-confirm-title="{{ $user->status === \App\Models\User::STATUS_ACTIVE ? 'Nonaktifkan akun pengguna?' : 'Aktifkan akun pengguna?' }}" data-confirm-description="{{ $user->status === \App\Models\User::STATUS_ACTIVE ? 'Akun '.$user->name.' tidak dapat masuk ke sistem sampai diaktifkan kembali.' : 'Akun '.$user->name.' dapat masuk kembali ke sistem.' }}" data-confirm-label="{{ $user->status === \App\Models\User::STATUS_ACTIVE ? 'Nonaktifkan' : 'Aktifkan' }}" data-confirm-tone="primary" data-confirm-icon="power">@csrf @method('PATCH')<button @class(['status-toggle-action', 'is-active' => $user->status === \App\Models\User::STATUS_ACTIVE]) type="submit" @disabled($user->is(auth()->user()))><x-lucide-power aria-hidden="true" /><span>{{ $user->status === \App\Models\User::STATUS_ACTIVE ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}</span></button></form>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-delete-confirm data-no-loading data-delete-name="Akun pengguna {{ $user->name }}" data-delete-description="Akun ini akan dihapus. Sistem tetap melindungi akun yang sedang digunakan dan minimal satu admin utama aktif.">@csrf @method('DELETE')<button class="danger-button" type="submit" @disabled($user->is(auth()->user()))><x-lucide-trash-2 aria-hidden="true" /><span>Hapus Pengguna</span></button></form>
            </div>
        </x-admin.panel>
    </div>
</x-layouts.admin>
