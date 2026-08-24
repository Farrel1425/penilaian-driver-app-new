@php
    $filterData = compact('filters', 'branches');

    if (isset($drivers)) {
        $filterData['drivers'] = $drivers;
    }

    if (isset($vehicles)) {
        $filterData['vehicles'] = $vehicles;
    }
@endphp

<x-admin.panel class="performance-report-toolbar">
    <div class="performance-report-toolbar-inner">
        @include('admin.assessments._filter', $filterData)

        <div class="page-inline-actions performance-report-actions">
            <a class="secondary-button" data-no-loading href="{{ route('admin.reports.export', ['type' => $type, ...$filters->queryString()]) }}">
                <x-lucide-download aria-hidden="true" />
                <span>Export Excel</span>
            </a>
            <a class="secondary-button" target="_blank" href="{{ route('admin.reports.print', ['type' => $type, ...$filters->queryString()]) }}">
                <x-lucide-printer aria-hidden="true" />
                <span>Simpan PDF</span>
            </a>
        </div>
    </div>
</x-admin.panel>
