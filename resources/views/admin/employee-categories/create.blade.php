<x-layouts.admin title="Tambah Kategori Pegawai">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.employee-categories.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    <x-admin.panel><form method="POST" action="{{ route('admin.employee-categories.store') }}">@include('admin.employee-categories._form')</form></x-admin.panel>
</x-layouts.admin>
