<x-layouts.admin title="Edit Driver">
    <div class="resource-page-navigation"><a class="primary-button" href="{{ $returnTo === 'detail' ? route('admin.drivers.show', $driver) : route('admin.drivers.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>
    <x-admin.panel><form method="POST" action="{{ route('admin.drivers.update', $driver) }}" enctype="multipart/form-data">@method('PUT') @include('admin.drivers._form')</form></x-admin.panel>
</x-layouts.admin>
