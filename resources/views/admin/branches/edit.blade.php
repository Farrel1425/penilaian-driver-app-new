<x-layouts.admin title="Edit Unit Kerja">
    <x-slot:pageActions><div class="resource-page-navigation">
        <a class="primary-button" href="{{ $returnTo === 'detail' ? route('admin.branches.show', $branch) : route('admin.branches.index') }}">
            <x-lucide-arrow-left aria-hidden="true" />
            <span>Kembali</span>
        </a>
    </div></x-slot>
    <x-admin.panel><form method="POST" action="{{ route('admin.branches.update', $branch) }}">@method('PUT') @include('admin.branches._form')</form></x-admin.panel>
</x-layouts.admin>
