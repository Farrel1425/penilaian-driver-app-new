<x-layouts.admin title="Tambah Admin">
    <x-admin.page-header title="Tambah Admin" description="Buat akun administrator baru untuk mengelola LAIS."><x-slot:actions><a href="{{ route('admin.users.index') }}" class="admin-secondary-button">Kembali</a></x-slot:actions></x-admin.page-header>
    @include('admin.users._form', ['user' => $user])
</x-layouts.admin>
