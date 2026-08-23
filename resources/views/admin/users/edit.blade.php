<x-layouts.admin title="Edit Admin">
    <x-admin.page-header title="Edit Admin" description="Perbarui data dan akses akun administrator."><x-slot:actions><a href="{{ route('admin.users.show', $user) }}" class="admin-secondary-button">Kembali</a></x-slot:actions></x-admin.page-header>
    @include('admin.users._form', ['user' => $user])
</x-layouts.admin>
