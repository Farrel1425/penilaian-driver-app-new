<x-layouts.admin title="Dashboard">
    @php
        $trend = $data['trend'];
        $chartWidth = max(760, $trend->count() * 76);
        $chartHeight = 220;
        $chartPadding = ['left' => 38, 'right' => 14, 'top' => 16, 'bottom' => 32];
        $chartInnerWidth = $chartWidth - $chartPadding['left'] - $chartPadding['right'];
        $chartInnerHeight = $chartHeight - $chartPadding['top'] - $chartPadding['bottom'];
        $dateLabelStep = max(1, (int) ceil($trend->count() / 8));
        $trendPoints = function (string $key) use ($trend, $chartPadding, $chartInnerWidth, $chartInnerHeight): string {
            $count = max($trend->count() - 1, 1);

            return $trend->map(function (array $point, int $index) use ($key, $count, $chartPadding, $chartInnerWidth, $chartInnerHeight): ?string {
                if ($point[$key] === null) {
                    return null;
                }

                $x = $chartPadding['left'] + ($index / $count) * $chartInnerWidth;
                $y = $chartPadding['top'] + (1 - ($point[$key] / 5)) * $chartInnerHeight;

                return round($x, 1).','.round($y, 1);
            })->filter()->implode(' ');
        };
    @endphp

    <x-admin.panel class="dashboard-filter-panel">
        <form class="dashboard-filter" method="GET" action="{{ route('admin.dashboard') }}">
            <div class="dashboard-filter-label"><x-lucide-calendar-days aria-hidden="true" /><span>Periode</span></div>
            <input type="date" name="start_date" value="{{ $filters->startDate?->toDateString() }}" aria-label="Tanggal mulai">
            <span class="dashboard-filter-divider">sampai</span>
            <input type="date" name="end_date" value="{{ $filters->endDate?->toDateString() }}" aria-label="Tanggal akhir">
            <select name="branch_id" aria-label="Filter unit kerja"><option value="">Semua Unit Kerja</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected($filters->branchId === $branch->id)>{{ $branch->name }}</option>@endforeach</select>
            <button class="secondary-button" type="submit"><x-lucide-filter aria-hidden="true" /><span>Terapkan</span></button>
            <a class="dashboard-filter-reset" href="{{ route('admin.dashboard') }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /></a>
        </form>
    </x-admin.panel>

    <section class="dashboard-stat-grid">
        <article class="dashboard-stat-card is-blue"><span class="dashboard-stat-icon"><x-lucide-clipboard-list aria-hidden="true" /></span><div><p>Total Penilaian</p><strong>{{ number_format($data['stats']['total_assessments']) }}</strong><small>Sesuai filter aktif</small></div></article>
        <article class="dashboard-stat-card is-green"><span class="dashboard-stat-icon"><x-lucide-user-round aria-hidden="true" /></span><div><p>Rata-rata Driver</p><strong>{{ $data['stats']['average_driver_rating'] ?? '-' }}</strong><small>Skala rating 1-5</small></div></article>
        <article class="dashboard-stat-card is-purple"><span class="dashboard-stat-icon"><x-lucide-car-front aria-hidden="true" /></span><div><p>Rata-rata Kendaraan</p><strong>{{ $data['stats']['average_vehicle_rating'] ?? '-' }}</strong><small>Skala rating 1-5</small></div></article>
        <article class="dashboard-stat-card is-orange"><span class="dashboard-stat-icon"><x-lucide-star aria-hidden="true" /></span><div><p>Driver Dinilai</p><strong>{{ $data['stats']['rated_drivers'] }}</strong><small>Driver unik</small></div></article>
        <article class="dashboard-stat-card is-teal"><span class="dashboard-stat-icon"><x-lucide-car aria-hidden="true" /></span><div><p>Kendaraan Dinilai</p><strong>{{ $data['stats']['rated_vehicles'] }}</strong><small>Kendaraan unik</small></div></article>
        <article class="dashboard-stat-card is-pink"><span class="dashboard-stat-icon"><x-lucide-chart-no-axes-combined aria-hidden="true" /></span><div><p>Penilaian Hari Ini</p><strong>{{ $data['stats']['today_assessments'] }}</strong><small>Tanggal hari ini</small></div></article>
    </section>

    <section class="dashboard-analytics-grid">
        <x-admin.panel class="dashboard-trend-card">
            <div class="dashboard-panel-heading"><div><h2>Trend Penilaian</h2><p><span class="dashboard-legend driver"></span>Driver <span class="dashboard-legend vehicle"></span>Kendaraan</p></div><div class="dashboard-chart-controls" data-chart-controls><button type="button" data-chart-zoom-out title="Perkecil grafik" aria-label="Perkecil grafik"><x-lucide-minus aria-hidden="true" /></button><span data-chart-zoom-label>100%</span><button type="button" data-chart-zoom-in title="Perbesar grafik" aria-label="Perbesar grafik"><x-lucide-plus aria-hidden="true" /></button></div></div>
            @if($trend->isNotEmpty())
                <p class="dashboard-chart-help"><x-lucide-move-horizontal aria-hidden="true" /> Gunakan Ctrl + scroll untuk zoom pada titik yang ditunjuk. Shift + scroll untuk menggeser tanggal.</p>
                <div class="dashboard-line-chart" data-chart-scroll tabindex="0" aria-label="Grafik trend dapat digeser horizontal"><div class="dashboard-chart-canvas" data-chart-canvas data-chart-base-width="{{ $chartWidth }}"><svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="Trend rata-rata penilaian driver dan kendaraan">
                    @foreach([0, 1, 2, 3, 4, 5] as $tick)
                        @php($y = $chartPadding['top'] + (1 - ($tick / 5)) * $chartInnerHeight)
                        <line x1="{{ $chartPadding['left'] }}" x2="{{ $chartWidth - $chartPadding['right'] }}" y1="{{ $y }}" y2="{{ $y }}" class="dashboard-chart-grid" />
                        <text x="{{ $chartPadding['left'] - 11 }}" y="{{ $y + 4 }}" class="dashboard-chart-tick">{{ $tick }}</text>
                    @endforeach
                    <polyline points="{{ $trendPoints('driver') }}" class="dashboard-chart-driver" />
                    <polyline points="{{ $trendPoints('vehicle') }}" class="dashboard-chart-vehicle" />
                    @foreach($trend as $index => $point)
                        @php($x = $chartPadding['left'] + ($index / max($trend->count() - 1, 1)) * $chartInnerWidth)
                        @if($point['driver'] !== null)
                            <circle cx="{{ $x }}" cy="{{ $chartPadding['top'] + (1 - ($point['driver'] / 5)) * $chartInnerHeight }}" r="4" class="dashboard-chart-driver-point"><title>{{ \Carbon\Carbon::parse($point['date'])->translatedFormat('d M Y') }} - Driver: {{ $point['driver'] }}</title></circle>
                        @endif
                        @if($point['vehicle'] !== null)
                            <circle cx="{{ $x }}" cy="{{ $chartPadding['top'] + (1 - ($point['vehicle'] / 5)) * $chartInnerHeight }}" r="4" class="dashboard-chart-vehicle-point"><title>{{ \Carbon\Carbon::parse($point['date'])->translatedFormat('d M Y') }} - Kendaraan: {{ $point['vehicle'] }}</title></circle>
                        @endif
                        @if($index % $dateLabelStep === 0 || $index === $trend->count() - 1)
                            <text x="{{ $x }}" y="{{ $chartHeight - 7 }}" text-anchor="middle" class="dashboard-chart-date">{{ \Carbon\Carbon::parse($point['date'])->format('d M') }}</text>
                        @endif
                    @endforeach
                </svg></div></div>
            @else
                <x-admin.empty-state title="Belum ada trend" description="Trend akan muncul setelah penilaian diterima." />
            @endif
        </x-admin.panel>
        <x-admin.panel class="dashboard-donut-panel"><div class="dashboard-panel-heading"><div><h2>Distribusi Driver</h2><p>Jawaban rating 1-5</p></div></div>@include('admin.partials.dashboard-donut', ['distribution' => $data['driverDistribution'], 'label' => 'Total Driver'])</x-admin.panel>
        <x-admin.panel class="dashboard-donut-panel"><div class="dashboard-panel-heading"><div><h2>Distribusi Kendaraan</h2><p>Jawaban rating 1-5</p></div></div>@include('admin.partials.dashboard-donut', ['distribution' => $data['vehicleDistribution'], 'label' => 'Total Kendaraan'])</x-admin.panel>
    </section>

    <section class="dashboard-data-grid">
        <x-admin.panel class="dashboard-latest-panel">
            <div class="dashboard-panel-heading"><div><h2>Penilaian Terbaru</h2><p>Aktivitas penumpang terkini</p></div><a href="{{ route('admin.assessments.index') }}">Lihat semua</a></div>
            <div class="table-wrap"><table class="data-table dashboard-table"><thead><tr><th>Waktu</th><th>Driver</th><th>Kendaraan</th><th>Unit Kerja</th><th>Driver</th><th>Kendaraan</th><th>Aksi</th></tr></thead><tbody>
                @forelse($data['latestRatings'] as $rating)
                    <tr><td>{{ $rating->submitted_at?->timezone(config('app.display_timezone'))?->format('d M H:i') }}</td><td>{{ $rating->driver?->full_name }}</td><td><strong>{{ $rating->vehicle?->police_number }}</strong><small>{{ $rating->vehicle?->brand }} {{ $rating->vehicle?->model }}</small></td><td>{{ $rating->branch?->name }}</td><td><span class="dashboard-score is-driver">{{ $analytics->ratingScore($rating, \App\Models\Question::TARGET_DRIVER) ?? '-' }}</span></td><td><span class="dashboard-score is-vehicle">{{ $analytics->ratingScore($rating, \App\Models\Question::TARGET_VEHICLE) ?? '-' }}</span></td><td><a class="dashboard-table-action" href="{{ route('admin.assessments.show', $rating) }}" aria-label="Lihat penilaian"><x-lucide-eye aria-hidden="true" /></a></td></tr>
                @empty
                    <tr><td colspan="7"><x-admin.empty-state title="Belum ada penilaian" description="Penilaian penumpang akan tampil di sini." /></td></tr>
                @endforelse
            </tbody></table></div>
        </x-admin.panel>
        <x-admin.panel><div class="dashboard-panel-heading"><div><h2>Penilaian per Unit Kerja</h2><p>Ringkasan sesuai filter</p></div><a href="{{ route('admin.reports.branches', $filters->queryString()) }}">Lihat semua</a></div><div class="table-wrap"><table class="data-table dashboard-table"><thead><tr><th>Unit Kerja</th><th>Driver</th><th>Kendaraan</th><th>Penilaian</th></tr></thead><tbody>@forelse($data['branchStats']->take(5) as $row)<tr><td><strong>{{ $row['branch'] }}</strong></td><td>{{ $row['driver_average'] ?? '-' }}</td><td>{{ $row['vehicle_average'] ?? '-' }}</td><td>{{ $row['total'] }}</td></tr>@empty<tr><td colspan="4"><x-admin.empty-state title="Belum ada data unit kerja" /></td></tr>@endforelse</tbody></table></div></x-admin.panel>
    </section>

    <section class="dashboard-bottom-grid">
        <x-admin.panel><div class="dashboard-panel-heading"><div><h2>Aktivitas Terkini</h2><p>Penilaian dan administrasi</p></div><a href="{{ route('admin.activity-logs.index') }}">Lihat semua</a></div><div class="dashboard-activity-list">@forelse($data['latestActivities'] as $activity)<div><span class="dashboard-activity-icon {{ $activity['type'] === 'rating' ? 'is-rating' : 'is-admin' }}">@if($activity['type'] === 'rating')<x-lucide-clipboard-check aria-hidden="true" />@else<x-lucide-shield-check aria-hidden="true" />@endif</span><p>{{ $activity['description'] }}<small>{{ $activity['created_at']?->diffForHumans() }}</small></p></div>@empty<x-admin.empty-state title="Belum ada aktivitas" />@endforelse</div></x-admin.panel>
        <x-admin.panel><div class="dashboard-panel-heading"><div><h2>Skor Unit Kerja</h2><p>Rata-rata driver dan kendaraan</p></div></div><div class="dashboard-branch-bars">@forelse($data['branchStats']->take(5) as $row)<div><div class="dashboard-branch-bar-label"><span>{{ $row['branch'] }}</span><strong>{{ $row['average'] ?? '-' }}</strong></div><div class="dashboard-bar-track"><i class="is-driver" style="width: {{ (($row['driver_average'] ?? 0) / 5) * 100 }}%"></i><i class="is-vehicle" style="width: {{ (($row['vehicle_average'] ?? 0) / 5) * 100 }}%"></i></div></div>@empty<x-admin.empty-state title="Belum ada skor unit kerja" />@endforelse</div></x-admin.panel>
        <x-admin.panel><div class="dashboard-panel-heading"><div><h2>Top Driver</h2><p>Berdasarkan rata-rata rating</p></div><a href="{{ route('admin.reports.drivers', $filters->queryString()) }}">Lihat semua</a></div><div class="dashboard-top-driver-list">@forelse($data['driverRanking'] as $index => $driver)<div><span class="dashboard-rank">{{ $index + 1 }}</span><span class="dashboard-driver-avatar">@if($driver['photo'])<img src="{{ str_starts_with($driver['photo'], 'http://') || str_starts_with($driver['photo'], 'https://') || str_starts_with($driver['photo'], '/') ? $driver['photo'] : asset('storage/'.$driver['photo']) }}" alt="">@else{{ strtoupper(substr($driver['name'], 0, 1)) }}@endif</span><p><strong>{{ $driver['name'] }}</strong><small>{{ $driver['branch'] }}</small></p><b>{{ $driver['average'] ?? '-' }}</b></div>@empty<x-admin.empty-state title="Belum ada ranking driver" />@endforelse</div></x-admin.panel>
    </section>
</x-layouts.admin>
