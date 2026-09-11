<x-layouts.admin title="Tambah Kategori Indikator">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.indicator-categories.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    <x-admin.panel><form method="POST" action="{{ route('admin.indicator-categories.store') }}">@include('admin.indicator-categories._form')</form></x-admin.panel>
</x-layouts.admin>
