<x-layouts.admin title="Tambah Pengguna">
    <div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.users.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>
    @include('admin.users._form', ['user' => $user])
</x-layouts.admin>
