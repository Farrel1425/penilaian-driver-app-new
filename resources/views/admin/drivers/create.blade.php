<x-layouts.admin title="Tambah Driver">
    <div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.drivers.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>
    <x-admin.panel><form method="POST" action="{{ route('admin.drivers.store') }}" enctype="multipart/form-data">@include('admin.drivers._form')</form></x-admin.panel>
</x-layouts.admin>
