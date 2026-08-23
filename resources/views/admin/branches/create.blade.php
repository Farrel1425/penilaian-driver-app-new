<x-layouts.admin title="Tambah Cabang">
    <div class="resource-page-navigation">
        <a class="primary-button" href="{{ route('admin.branches.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a>
    </div>
    <x-admin.panel><form method="POST" action="{{ route('admin.branches.store') }}">@include('admin.branches._form')</form></x-admin.panel>
</x-layouts.admin>
