<x-layouts.admin title="Laporan Monitoring">
    <x-slot:pageActions><div class="monitoring-report-actions"><a class="secondary-button" href="{{ $branch ? route('admin.monitoring.show', [$branch, 'period' => $period->format('Y-m')]) : route('admin.monitoring.index', ['period' => $period->format('Y-m')]) }}"><x-lucide-arrow-left aria-hidden="true" /> Kembali</a><a class="primary-button" data-no-loading href="{{ $branch ? route('admin.monitoring.branch.export', [$branch, 'period' => $period->format('Y-m')]) : route('admin.monitoring.export', ['period' => $period->format('Y-m')]) }}"><x-lucide-download aria-hidden="true" /> Download PDF</a></div></x-slot>
    <div class="monitoring-report-shell">
        <header><span class="monitoring-modal-icon"><x-lucide-file-down aria-hidden="true" /></span><div><h2>Preview Dokumen</h2><p>Laporan Monitoring periode {{ $period->translatedFormat('F Y') }}</p></div></header>
        @foreach($reports as $report)
            @include('admin.monitoring._report-document', compact('report', 'period'))
        @endforeach
    </div>
</x-layouts.admin>
