<x-layouts.admin title="Tambah Pertanyaan">
    <div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.questions.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>
    <x-admin.panel><form method="POST" action="{{ route('admin.questions.store') }}">@include('admin.questions._form')</form></x-admin.panel>
</x-layouts.admin>
