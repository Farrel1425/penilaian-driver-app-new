<x-layouts.admin title="Monitoring">
    <x-slot:pageHeading>Monitoring Operasional</x-slot>
    <x-slot:pageDescription>Pantau penilaian driver, kendaraan, dan kelengkapan setiap unit kerja.</x-slot>
    <x-slot:pageActions>
        <form class="monitoring-period-panel" method="GET">
            <label class="native-picker-field"><span>Periode</span><input type="month" name="period" value="{{ $period->format('Y-m') }}" onchange="this.form.requestSubmit()"></label>
            <input type="hidden" name="target" value="{{ $target }}"><input type="hidden" name="status" value="{{ $status }}"><input type="hidden" name="search" value="{{ request('search') }}">
            @if (request()->filled('period') || request()->filled('status') || request()->filled('search'))
                <a class="secondary-button assessment-reset-button" href="{{ route('admin.monitoring.index', ['target' => $target]) }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>
            @endif
            <a class="monitoring-print-button" data-no-loading href="{{ route('admin.monitoring.report', ['period' => $period->format('Y-m')]) }}" title="Cetak laporan driver dan kendaraan"><x-lucide-printer aria-hidden="true" /><span>Cetak</span></a>
        </form>
    </x-slot>

    <nav class="recruitment-tabs" aria-label="Jenis data monitoring">
        <a @class(['is-active' => $target === 'driver']) href="{{ route('admin.monitoring.index', array_filter(['period' => $period->format('Y-m'), 'target' => 'driver', 'search' => request('search')])) }}"><x-lucide-users aria-hidden="true" /><span>Driver</span></a>
        <a @class(['is-active' => $target === 'vehicle']) href="{{ route('admin.monitoring.index', array_filter(['period' => $period->format('Y-m'), 'target' => 'vehicle', 'search' => request('search')])) }}"><x-lucide-car aria-hidden="true" /><span>Kendaraan</span></a>
    </nav>

    <section class="monitoring-panel">
        <header class="monitoring-panel-header">
            <div><h3>{{ $target === 'vehicle' ? 'Penilaian Kendaraan per Unit Kerja' : 'Kelengkapan Driver per Unit Kerja' }}</h3><p>Klik unit kerja untuk melihat data {{ $target === 'vehicle' ? 'kendaraan' : 'driver' }} secara rinci.</p></div>
            <nav class="monitoring-status-tabs" aria-label="Filter status">
                <a @class(['is-active' => !in_array($status, ['complete', 'incomplete'], true)]) href="{{ route('admin.monitoring.index', array_filter(['period' => $period->format('Y-m'), 'target' => $target, 'search' => request('search')])) }}">Semua</a>
                <a @class(['is-active' => $status === 'complete']) href="{{ route('admin.monitoring.index', array_filter(['period' => $period->format('Y-m'), 'target' => $target, 'status' => 'complete', 'search' => request('search')])) }}">{{ $target === 'vehicle' ? 'Sudah Dinilai' : 'Lengkap' }}</a>
                <a @class(['is-active' => $status === 'incomplete']) href="{{ route('admin.monitoring.index', array_filter(['period' => $period->format('Y-m'), 'target' => $target, 'status' => 'incomplete', 'search' => request('search')])) }}">{{ $target === 'vehicle' ? 'Belum Dinilai' : 'Belum Lengkap' }}</a>
            </nav>
        </header>
        <div class="table-wrap"><table class="data-table monitoring-table">
            @if($target === 'vehicle')
                <thead><tr><th>No</th><th>Kode</th><th>Unit Kerja</th><th>Wilayah</th><th>Kendaraan Aktif</th><th>Sudah Dinilai</th><th>Total Penilaian</th><th>Nilai Kendaraan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>@forelse($rows as $row)<tr>
                    <td>{{ str_pad((string) (($rows->firstItem() ?? 1) + $loop->index), 2, '0', STR_PAD_LEFT) }}</td><td><span class="monitoring-code">{{ $row['branch']->code }}</span></td>
                    <td><strong>{{ $row['branch']->name }}</strong><small>{{ $row['branch']->address ?: 'Alamat belum tersedia' }}</small></td><td>{{ $row['branch']->regency ?: '-' }}</td>
                    <td><strong>{{ $row['vehicles'] }}</strong></td><td><strong>{{ $row['vehicles_rated'] }}</strong></td><td><strong>{{ $row['vehicle_rating_count'] }}</strong></td><td><strong class="monitoring-score">{{ $row['vehicle_average'] !== null ? number_format($row['vehicle_average'], 1) : '-' }}</strong></td>
                    <td><div class="monitoring-completeness-cell"><span @class(['monitoring-completeness', 'is-complete' => $row['is_vehicle_complete'], 'is-incomplete' => !$row['is_vehicle_complete']])>{{ $row['is_vehicle_complete'] ? 'Sudah Dinilai' : 'Belum Dinilai' }}</span><small>{{ $row['vehicles_rated'] }}/{{ $row['vehicles'] }} kendaraan</small></div></td>
                    <td><div class="monitoring-row-actions"><a class="monitoring-view-button" href="{{ route('admin.monitoring.show', [$row['branch'], 'period' => $period->format('Y-m'), 'target' => 'vehicle']) }}"><x-lucide-car aria-hidden="true" /><span>Lihat</span></a><a class="monitoring-icon-button" data-no-loading href="{{ route('admin.monitoring.branch.report', [$row['branch'], 'period' => $period->format('Y-m')]) }}" title="Preview laporan"><x-lucide-printer aria-hidden="true" /></a></div></td>
                </tr>@empty<tr><td colspan="10"><x-admin.empty-state title="Belum ada unit kerja" description="Tidak ada data kendaraan yang sesuai dengan filter." /></td></tr>@endforelse</tbody>
            @else
                <thead><tr><th>No</th><th>Kode</th><th>Unit Kerja</th><th>Wilayah</th><th>Armada</th><th>Driver</th><th>Rata-rata Absensi</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>@forelse($rows as $row)<tr>
                    <td>{{ str_pad((string) (($rows->firstItem() ?? 1) + $loop->index), 2, '0', STR_PAD_LEFT) }}</td><td><span class="monitoring-code">{{ $row['branch']->code }}</span></td><td><strong>{{ $row['branch']->name }}</strong><small>{{ $row['branch']->address ?: 'Alamat belum tersedia' }}</small></td><td>{{ $row['branch']->regency ?: '-' }}</td><td><strong>{{ $row['vehicles'] }}</strong><small>Kendaraan aktif</small></td><td><strong>{{ $row['drivers'] }}</strong><small>Driver aktif</small></td><td><strong class="monitoring-score">{{ $row['attendance_average'] !== null ? number_format($row['attendance_average'], 1).'%' : '-' }}</strong></td>
                    <td><div class="monitoring-completeness-cell"><span @class(['monitoring-completeness', 'is-complete' => $row['is_complete'], 'is-incomplete' => !$row['is_complete']])>{{ $row['is_complete'] ? 'Lengkap' : 'Belum Lengkap' }}</span><small>{{ $row['completed'] }}/{{ $row['drivers'] }} driver</small></div></td>
                    <td><div class="monitoring-row-actions"><a class="monitoring-view-button" href="{{ route('admin.monitoring.show', [$row['branch'], 'period' => $period->format('Y-m'), 'target' => 'driver']) }}"><x-lucide-users aria-hidden="true" /><span>Lihat</span></a><a class="monitoring-icon-button" data-no-loading href="{{ route('admin.monitoring.branch.report', [$row['branch'], 'period' => $period->format('Y-m')]) }}" title="Preview laporan"><x-lucide-printer aria-hidden="true" /></a></div></td>
                </tr>@empty<tr><td colspan="9"><x-admin.empty-state title="Belum ada unit kerja" description="Tidak ada data driver yang sesuai dengan filter." /></td></tr>@endforelse</tbody>
            @endif
        </table></div>
        <footer class="monitoring-panel-footer"><span>Menampilkan {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} unit kerja</span>@if($rows->hasPages())<x-admin.pagination :paginator="$rows" label="Pagination monitoring" />@endif</footer>
    </section>
</x-layouts.admin>
