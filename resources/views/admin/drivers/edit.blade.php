<x-layouts.admin title="Edit Pegawai">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ $returnTo === 'detail' ? route('admin.employees.show', $driver) : route('admin.employees.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    <x-admin.panel><form method="POST" action="{{ route('admin.employees.update', $driver) }}" enctype="multipart/form-data">@method('PUT') @include('admin.drivers._form')</form></x-admin.panel>
</x-layouts.admin>
