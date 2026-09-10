<x-layouts.admin title="Tambah Pengguna">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.users.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    @include('admin.users._form', ['user' => $user])
</x-layouts.admin>
