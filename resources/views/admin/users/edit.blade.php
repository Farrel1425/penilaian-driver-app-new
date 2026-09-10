<x-layouts.admin title="Edit Pengguna">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ $returnTo === 'detail' ? route('admin.users.show', $user) : route('admin.users.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    @include('admin.users._form', ['user' => $user, 'returnTo' => $returnTo])
</x-layouts.admin>
