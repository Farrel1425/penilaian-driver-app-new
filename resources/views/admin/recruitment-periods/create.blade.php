<x-layouts.admin title="Tambah Gelombang Recruitment">
    <x-slot:pageActions><a class="primary-button" href="{{ route('admin.recruitment-periods.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></x-slot:pageActions>
    <x-admin.panel><form method="POST" action="{{ route('admin.recruitment-periods.store') }}">@include('admin.recruitment-periods._form')</form></x-admin.panel>
</x-layouts.admin>
