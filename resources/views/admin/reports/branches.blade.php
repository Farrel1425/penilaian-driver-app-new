<x-layouts.admin title="Report Unit Kerja">
    <x-slot:pageActions>@include('admin.reports._toolbar', ['type' => 'branch'])</x-slot>

    <div class="stat-grid report-stat-grid">
        <x-admin.stat-card label="Total Unit Kerja" :value="$data['stats']['total_branches']" note="Memiliki penilaian sesuai filter" />
        <x-admin.stat-card label="Rating Unit" :value="$data['stats']['average_rating'] ?? '-'" note="Hanya jawaban rating 1-5" />
        <x-admin.stat-card label="Total Penilaian" :value="$data['stats']['total_assessments']" note="Penilaian masuk sesuai filter" />
        <x-admin.stat-card label="Unit Terbaik" :value="$data['stats']['top_branch']" note="Berdasarkan rating rata-rata" />
    </div>

    <div class="dashboard-grid report-insight-grid">
        <x-admin.panel title="Rating Unit Kerja" description="Nilai rata-rata seluruh unit kerja sesuai filter aktif.">
            @include('admin.partials.rating-gauge', ['value' => $data['stats']['average_rating'], 'label' => 'Rating rata-rata unit kerja'])
        </x-admin.panel>
        <x-admin.panel title="Distribusi Rating" description="Sebaran jawaban rating 1 sampai 5.">
            @include('admin.partials.distribution', ['distribution' => $data['distribution']])
        </x-admin.panel>
    </div>

    <x-admin.panel title="Ranking Unit Kerja" description="Ringkasan driver, kendaraan, penilaian, serta performa terbaik pada setiap unit kerja.">
        <div class="table-wrap"><table class="data-table report-performance-table"><thead><tr><th>Rank</th><th>Unit Kerja</th><th>Driver</th><th>Kendaraan</th><th>Total Penilaian</th><th>Rating Unit</th><th>Top Driver</th><th>Top Kendaraan</th></tr></thead><tbody>@forelse($data['rows'] as $row)<tr><td><span class="report-rank">{{ $loop->iteration }}</span></td><td><strong>{{ $row['branch'] }}</strong></td><td>{{ $row['drivers'] }}</td><td>{{ $row['vehicles'] }}</td><td>{{ $row['total'] }}</td><td><strong class="report-average">{{ $row['average'] ?? '-' }}</strong></td><td>{{ $row['top_driver'] }}</td><td>{{ $row['top_vehicle'] }}</td></tr>@empty<tr><td colspan="8"><x-admin.empty-state title="Belum ada data unit kerja" /></td></tr>@endforelse</tbody></table></div>
    </x-admin.panel>
</x-layouts.admin>
