<x-layouts.admin title="Dashboard">
@php
    $stats = $data['stats'];
    $trend = $data['trend']->take(-8)->values();
    $trendMax = max(5, (float) $trend->flatMap(fn ($row) => [$row['driver'] ?? 0, $row['vehicle'] ?? 0])->max());
    $driverDistribution = $data['driverDistribution'];
    $vehicleDistribution = $data['vehicleDistribution'];
    $distributionMax = max(1, ...array_values($driverDistribution), ...array_values($vehicleDistribution));
    $branchStats = $data['branchStats']->take(5);
    $branchMax = max(5, (float) $branchStats->max('average'));
    $displayTimezone = config('app.display_timezone');
    $filterQuery = $filters->queryString();
    $today = now($displayTimezone)->toDateString();
    $todayQuery = array_merge($filterQuery, ['start_date' => $today, 'end_date' => $today]);
    $canViewActivityLogs = auth()->user()?->role === \App\Models\User::ROLE_ADMIN;
    $activityIndexUrl = $canViewActivityLogs
        ? route('admin.activity-logs.index')
        : route('admin.assessments.index', $filterQuery);
    $activityIndexLabel = $canViewActivityLogs ? 'Lihat Semua Aktivitas' : 'Lihat Semua Penilaian';
@endphp

<section class="admin-dashboard">
    <header class="admin-dashboard-heading">
        <div><p>DASHBOARD</p><h1>Dashboard Utama</h1></div>
        <form class="admin-dashboard-filters" method="GET" action="{{ route('admin.dashboard') }}" data-dashboard-filter-form>
            <input type="hidden" name="search" value="{{ $filters->search }}">
            <label><span>Mulai</span><input type="date" name="start_date" value="{{ $filters->startDate?->timezone($displayTimezone)->toDateString() }}" data-dashboard-filter></label>
            <label><span>Sampai</span><input type="date" name="end_date" value="{{ $filters->endDate?->timezone($displayTimezone)->toDateString() }}" data-dashboard-filter></label>
            <label><span>Unit Kerja</span><select name="branch_id" data-dashboard-filter><option value="">Semua Unit Kerja</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}" @selected($filters->branchId === $branch->id)>{{ $branch->name }}</option>@endforeach</select></label>
            @if (count($filters->queryString()))
                <a class="secondary-button assessment-reset-button dashboard-filter-reset" href="{{ route('admin.dashboard') }}"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>
            @endif
        </form>
    </header>

    <div class="ui-metric-grid ui-metric-grid--six">
        <a class="ui-metric-card" href="{{ route('admin.assessments.index', $filterQuery) }}" aria-label="Buka riwayat seluruh penilaian"><p>Total Penilaian</p><i class="ink"></i><strong>{{ number_format($stats['total_assessments'] ?? 0) }}</strong><small>Dalam periode terpilih</small><x-lucide-arrow-up-right class="ui-metric-link-icon" aria-hidden="true" /></a>
        <a class="ui-metric-card" href="{{ route('admin.reports.drivers', $filterQuery) }}" aria-label="Buka laporan rata-rata driver"><p>Rata-rata Driver</p><i class="lime"></i><strong>{{ number_format($stats['average_driver_rating'] ?? 0, 2) }}</strong><small>Dari skala 5.00</small><x-lucide-arrow-up-right class="ui-metric-link-icon" aria-hidden="true" /></a>
        <a class="ui-metric-card" href="{{ route('admin.reports.vehicles', $filterQuery) }}" aria-label="Buka laporan rata-rata kendaraan"><p>Rata-rata Kendaraan</p><i class="blue"></i><strong>{{ number_format($stats['average_vehicle_rating'] ?? 0, 2) }}</strong><small>Dari skala 5.00</small><x-lucide-arrow-up-right class="ui-metric-link-icon" aria-hidden="true" /></a>
        <a class="ui-metric-card" href="{{ route('admin.reports.drivers', $filterQuery) }}" aria-label="Buka laporan driver yang dinilai"><p>Driver Dinilai</p><i class="green"></i><strong>{{ number_format($stats['rated_drivers'] ?? 0) }}</strong><small>Driver unik</small><x-lucide-arrow-up-right class="ui-metric-link-icon" aria-hidden="true" /></a>
        <a class="ui-metric-card" href="{{ route('admin.reports.vehicles', $filterQuery) }}" aria-label="Buka laporan kendaraan yang dinilai"><p>Kendaraan Dinilai</p><i class="amber"></i><strong>{{ number_format($stats['rated_vehicles'] ?? 0) }}</strong><small>Kendaraan unik</small><x-lucide-arrow-up-right class="ui-metric-link-icon" aria-hidden="true" /></a>
        <a class="ui-metric-card" href="{{ route('admin.assessments.index', $todayQuery) }}" aria-label="Buka riwayat penilaian hari ini"><p>Penilaian Hari Ini</p><i class="red"></i><strong>{{ number_format($stats['today_assessments'] ?? 0) }}</strong><small>Waktu lokal sistem</small><x-lucide-arrow-up-right class="ui-metric-link-icon" aria-hidden="true" /></a>
    </div>

    <div class="admin-dashboard-middle">
        <a class="ui-dashboard-panel-link" href="{{ route('admin.assessments.index', $filterQuery) }}" aria-label="Buka riwayat penilaian untuk periode terpilih"><article class="ui-trend">
            <header><div><h2>Tren Penilaian</h2><p>Rata-rata skor driver dan kendaraan dari waktu ke waktu</p></div><div class="ui-chart-legend"><span><i class="driver"></i>Driver</span><span><i class="vehicle"></i>Kendaraan</span></div></header>
            <div class="ui-bar-groups">
                @forelse ($trend as $row)
                    <div class="ui-bar-group"><div class="ui-bar-pair"><i class="driver" title="Driver: {{ number_format($row['driver'] ?? 0, 2) }}" style="height: {{ max(8, (($row['driver'] ?? 0) / $trendMax) * 190) }}px"></i><i class="vehicle" title="Kendaraan: {{ number_format($row['vehicle'] ?? 0, 2) }}" style="height: {{ max(8, (($row['vehicle'] ?? 0) / $trendMax) * 190) }}px"></i></div><small>{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('d M') }}</small></div>
                @empty
                    <p class="ui-empty">Belum ada data tren pada periode ini.</p>
                @endforelse
            </div>
        </article></a>

        <article class="ui-activity">
            <header><h2>Aktivitas Terkini</h2><x-lucide-ellipsis aria-hidden="true" /></header>
            <div class="ui-activity-list">
                @forelse ($data['latestActivities']->take(4) as $activity)
                    <a href="{{ $activity['url'] }}"><span class="{{ $activity['type'] === 'rating' ? 'ok' : 'warn' }}">@if ($activity['type'] === 'rating')<x-lucide-circle-check aria-hidden="true" />@else<x-lucide-clipboard-list aria-hidden="true" />@endif</span><p>{{ $activity['description'] }}<small>{{ $activity['created_at']?->diffForHumans() }}</small></p><x-lucide-chevron-right class="ui-activity-link-icon" aria-hidden="true" /></a>
                @empty
                    <p class="ui-empty">Belum ada aktivitas.</p>
                @endforelse
            </div>
            <a href="{{ $activityIndexUrl }}">{{ $activityIndexLabel }}</a>
        </article>
    </div>

    <div class="ui-insight-grid">
        <article class="ui-insight-card ui-distribution-card">
            <header><div><h2>Distribusi Penilaian</h2><p>Jumlah jawaban pada setiap skor</p></div></header>
            <div class="ui-distribution-columns">
                <div><h3>Driver</h3>@foreach ($driverDistribution as $score => $count)<div class="ui-distribution-row"><span>{{ $score }}</span><i><b style="width: {{ ($count / $distributionMax) * 100 }}%"></b></i><strong>{{ $count }}</strong></div>@endforeach</div>
                <div><h3>Kendaraan</h3>@foreach ($vehicleDistribution as $score => $count)<div class="ui-distribution-row vehicle"><span>{{ $score }}</span><i><b style="width: {{ ($count / $distributionMax) * 100 }}%"></b></i><strong>{{ $count }}</strong></div>@endforeach</div>
            </div>
        </article>

        <article class="ui-insight-card ui-branch-score-card">
            <header><div><h2>Skor Rata-rata Unit Kerja</h2><p>Unit kerja dengan data penilaian terbanyak</p></div></header>
            <div class="ui-branch-score-list">
                @forelse ($branchStats as $branch)
                    <div><span>{{ $branch['branch'] }}</span><i><b style="width: {{ (($branch['average'] ?? 0) / $branchMax) * 100 }}%"></b></i><strong>{{ number_format($branch['average'] ?? 0, 2) }}</strong></div>
                @empty
                    <p class="ui-empty">Belum ada skor Unit Kerja.</p>
                @endforelse
            </div>
        </article>

        <article class="ui-insight-card ui-top-driver-card">
            <header><div><h2>Top Driver</h2><p>Berdasarkan rata-rata skor</p></div><x-lucide-trophy aria-hidden="true" /></header>
            <ol class="ui-top-driver-list">
                @forelse ($data['driverRanking'] as $driver)
                    <li><span>{{ $loop->iteration }}</span><p><b>{{ $driver['name'] }}</b><small>{{ $driver['branch'] ?? 'Tanpa Unit Kerja' }} · {{ $driver['total'] }} penilaian</small></p><strong>{{ number_format($driver['average'] ?? 0, 2) }}</strong></li>
                @empty
                    <li class="ui-empty">Belum ada peringkat driver.</li>
                @endforelse
            </ol>
        </article>
    </div>

    <div class="admin-dashboard-tables">
        <article class="ui-latest">
            <header><div><h2>Penilaian Terbaru</h2><p>Penilaian yang terakhir masuk</p></div><x-lucide-filter aria-hidden="true" /></header>
            <div class="table-wrap"><table><thead><tr><th>DRIVER</th><th>KENDARAAN</th><th>UNIT KERJA</th><th>SKOR</th><th>TANGGAL</th><th class="ui-latest-action-heading">AKSI</th></tr></thead><tbody>
                @forelse ($data['latestRatings']->take(5) as $rating)
                    <tr><td>{{ $rating->driver?->full_name ?? '-' }}</td><td>{{ $rating->vehicle?->police_number ?? '-' }}</td><td>{{ $rating->branch?->name ?? '-' }}</td><td><b class="ui-score">{{ number_format($analytics->ratingScore($rating) ?? 0, 2) }}</b></td><td>{{ $rating->submitted_at?->timezone($displayTimezone)?->format('d M, H:i') ?? '-' }}</td><td class="ui-latest-action-cell"><div class="table-row-actions"><a class="ui-latest-view-action" href="{{ route('admin.assessments.show', $rating) }}" aria-label="Lihat detail penilaian" title="Lihat detail"><x-lucide-eye aria-hidden="true" /></a></div></td></tr>
                @empty
                    <tr><td colspan="6">Belum ada penilaian.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>

        <article class="ui-latest ui-branch-table">
            <header><div><h2>Penilaian Unit Kerja</h2><p>Ringkasan performa setiap unit kerja</p></div><x-lucide-building-2 aria-hidden="true" /></header>
            <div class="table-wrap"><table><thead><tr><th>UNIT KERJA</th><th>PENILAIAN</th><th>RATA-RATA</th><th>DRIVER</th><th>KENDARAAN</th></tr></thead><tbody>
                @forelse ($data['branchStats'] as $branch)
                    <tr><td>{{ $branch['branch'] }}</td><td>{{ number_format($branch['total']) }}</td><td><b class="ui-score">{{ number_format($branch['average'] ?? 0, 2) }}</b></td><td>{{ number_format($branch['driver_average'] ?? 0, 2) }}</td><td>{{ number_format($branch['vehicle_average'] ?? 0, 2) }}</td></tr>
                @empty
                    <tr><td colspan="5">Belum ada data unit kerja.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>
    </div>
</section>
</x-layouts.admin>
