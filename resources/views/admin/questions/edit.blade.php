<x-layouts.admin title="Edit Pertanyaan">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ $returnTo === 'detail' ? route('admin.questions.show', $question) : route('admin.questions.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    <x-admin.panel><form method="POST" action="{{ route('admin.questions.update', $question) }}" enctype="multipart/form-data">@method('PUT') @include('admin.questions._form')</form></x-admin.panel>
</x-layouts.admin>
