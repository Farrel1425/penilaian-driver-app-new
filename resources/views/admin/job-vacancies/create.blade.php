<x-layouts.admin title="Tambah Lowongan">
    <x-slot:pageActions><a class="primary-button" href="{{ route('admin.job-vacancies.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></x-slot:pageActions>
    <x-admin.panel><form method="POST" action="{{ route('admin.job-vacancies.store') }}">@include('admin.job-vacancies._form')</form></x-admin.panel>
</x-layouts.admin>
