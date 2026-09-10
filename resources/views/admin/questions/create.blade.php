<x-layouts.admin title="Tambah Pertanyaan">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.questions.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    <x-admin.panel><form method="POST" action="{{ route('admin.questions.store') }}" enctype="multipart/form-data">@include('admin.questions._form')</form></x-admin.panel>
</x-layouts.admin>
