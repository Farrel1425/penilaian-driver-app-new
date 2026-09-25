<x-layouts.admin title="Monitoring">
    <x-slot:pageHeading>Monitoring Operasional &amp; Kelengkapan Cabang</x-slot>
    <x-slot:pageDescription>Pantau penilaian, absensi, dan kelengkapan setiap unit kerja.</x-slot>
    <x-slot:pageActions>
        <form class="monitoring-period-panel" method="GET">
            <label class="native-picker-field"><span>Periode</span><input type="month" name="period" value="{{ $period->format('Y-m') }}" onchange="this.form.requestSubmit()"></label>
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            @if (request()->filled('period') || request()->filled('status') || request()->filled('search'))
                <a class="secondary-button assessment-reset-button" href="{{ route('admin.monitoring.index') }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>
            @endif
            <a class="monitoring-print-button" data-no-loading href="{{ route('admin.monitoring.report', ['period' => $period->format('Y-m')]) }}" title="Cetak laporan" aria-label="Cetak laporan"><x-lucide-printer aria-hidden="true" /><span>Cetak</span></a>
        </form>
    </x-slot>

    <section class="monitoring-panel">
        <header class="monitoring-panel-header">
            <div><h3>Daftar Unit Kerja &amp; Status Cabang Bali</h3><p>Klik aksi pada cabang untuk meninjau personel driver operasional.</p></div>
            <nav class="monitoring-status-tabs" aria-label="Filter status kelengkapan">
                <a @class(['is-active' => !in_array($status, ['complete', 'incomplete'], true)]) href="{{ route('admin.monitoring.index', array_filter(['period' => $period->format('Y-m'), 'search' => request('search')])) }}">Semua <b>{{ $rows->total() }}</b></a>
                <a @class(['is-active' => $status === 'complete']) href="{{ route('admin.monitoring.index', array_filter(['period' => $period->format('Y-m'), 'status' => 'complete', 'search' => request('search')])) }}">Lengkap</a>
                <a @class(['is-active' => $status === 'incomplete']) href="{{ route('admin.monitoring.index', array_filter(['period' => $period->format('Y-m'), 'status' => 'incomplete', 'search' => request('search')])) }}">Belum Lengkap</a>
            </nav>
        </header>
        <div class="table-wrap">
            <table class="data-table monitoring-table">
                <thead><tr><th>No</th><th>Kode Cabang</th><th>Nama Unit Kerja</th><th>Wilayah Operasional</th><th>Armada</th><th>Driver</th><th>Rata-rata Absensi</th><th>Status Kelengkapan</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ str_pad((string) (($rows->firstItem() ?? 1) + $loop->index), 2, '0', STR_PAD_LEFT) }}</td>
                            <td><span class="monitoring-code">{{ $row['branch']->code }}</span></td>
                            <td><strong>{{ $row['branch']->name }}</strong><small>{{ $row['branch']->address ?: 'Alamat belum tersedia' }}</small></td>
                            <td>{{ $row['branch']->regency ?: '-' }}</td>
                            <td><strong>{{ $row['vehicles'] }}</strong><small>Kendaraan aktif</small></td>
                            <td><strong>{{ $row['drivers'] }}</strong><small>Driver aktif</small></td>
                            <td><strong class="monitoring-score">{{ $row['attendance_average'] !== null ? number_format($row['attendance_average'], 1).'%' : '-' }}</strong></td>
                            <td><div class="monitoring-completeness-cell"><span @class(['monitoring-completeness', 'is-complete' => $row['is_complete'], 'is-incomplete' => !$row['is_complete']])><x-lucide-circle-check aria-hidden="true" />{{ $row['is_complete'] ? 'Lengkap' : 'Belum Lengkap' }}</span><small>{{ $row['completed'] }}/{{ $row['drivers'] }} driver</small></div></td>
                            <td><div class="monitoring-row-actions"><a class="monitoring-view-button" href="{{ route('admin.monitoring.show', [$row['branch'], 'period' => $period->format('Y-m')]) }}"><x-lucide-users aria-hidden="true" /><span>Lihat Driver</span></a><a class="monitoring-icon-button" data-no-loading href="{{ route('admin.monitoring.branch.report', [$row['branch'], 'period' => $period->format('Y-m')]) }}" title="Preview laporan" aria-label="Preview laporan {{ $row['branch']->name }}"><x-lucide-printer aria-hidden="true" /></a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><x-admin.empty-state title="Belum ada unit kerja" description="Tidak ada unit kerja yang sesuai dengan filter Monitoring." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="monitoring-panel-footer"><span>Menampilkan {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} unit kerja</span>@if($rows->hasPages())<x-admin.pagination :paginator="$rows" label="Pagination monitoring" />@endif</footer>
    </section>
</x-layouts.admin>
