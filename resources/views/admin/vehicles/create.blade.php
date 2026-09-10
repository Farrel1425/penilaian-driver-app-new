<x-layouts.admin title="Tambah Kendaraan">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.vehicles.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    <x-admin.panel><form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data">@include('admin.vehicles._form')</form></x-admin.panel>
</x-layouts.admin>
