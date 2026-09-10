@php
    $filterData = compact('filters', 'branches');

    if (isset($drivers)) {
        $filterData['drivers'] = $drivers;
    }

    if (isset($vehicles)) {
        $filterData['vehicles'] = $vehicles;
    }
@endphp

<div class="performance-report-toolbar-inner">
        @include('admin.assessments._filter', [...$filterData, 'searchPlaceholder' => $type === 'branch' ? 'Cari unit kerja' : 'Cari driver, kendaraan, atau unit kerja'])

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
