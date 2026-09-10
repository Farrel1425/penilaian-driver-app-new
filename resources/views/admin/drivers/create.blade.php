<x-layouts.admin title="Tambah Pegawai">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.employees.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    <x-admin.panel><form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data">@include('admin.drivers._form')</form></x-admin.panel>
</x-layouts.admin>
