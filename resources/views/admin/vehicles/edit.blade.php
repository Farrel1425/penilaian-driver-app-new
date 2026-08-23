<x-layouts.admin title="Edit Kendaraan">
    <div class="resource-page-navigation"><a class="primary-button" href="{{ $returnTo === 'detail' ? route('admin.vehicles.show', $vehicle) : route('admin.vehicles.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>
    <x-admin.panel><form method="POST" action="{{ route('admin.vehicles.update', $vehicle) }}" enctype="multipart/form-data">@method('PUT') @include('admin.vehicles._form')</form></x-admin.panel>
</x-layouts.admin>
