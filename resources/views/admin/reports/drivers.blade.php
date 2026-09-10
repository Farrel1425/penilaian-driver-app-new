<x-layouts.admin title="Report Driver">
    <x-slot:pageActions>@include('admin.reports._toolbar', ['type' => 'driver'])</x-slot>

    <div class="stat-grid report-stat-grid">
        <x-admin.stat-card label="Total Driver" :value="$data['stats']['total_driver']" note="Sesuai unit kerja terpilih" />
        <x-admin.stat-card label="Rating Rata-rata" :value="$data['stats']['average_rating'] ?? '-'" note="Hanya jawaban rating 1-5" />
        <x-admin.stat-card label="Total Penilaian" :value="$data['stats']['total_assessments']" note="Penilaian driver unik" />
        <x-admin.stat-card label="Driver Terbaik" :value="$data['performance']->first()['name'] ?? '-'" note="Berdasarkan rating rata-rata" />
    </div>

    <div class="dashboard-grid report-insight-grid">
        <x-admin.panel title="Rating Driver" description="Nilai rata-rata penilaian driver sesuai filter aktif.">
            @include('admin.partials.rating-gauge', ['value' => $data['stats']['average_rating'], 'label' => 'Rating rata-rata driver'])
        </x-admin.panel>
        <x-admin.panel title="Distribusi Rating" description="Sebaran jawaban rating 1 sampai 5.">
            @include('admin.partials.distribution', ['distribution' => $data['distribution']])
        </x-admin.panel>
    </div>

    <div class="dashboard-grid report-insight-grid">
        <x-admin.panel title="Nilai per Pertanyaan" description="Rata-rata setiap pertanyaan khusus penilaian driver.">
            <div class="table-wrap report-question-table"><table class="data-table"><thead><tr><th>No.</th><th>Indikator</th><th>Pertanyaan</th><th>Rata-rata</th><th>Penilaian</th></tr></thead><tbody>@forelse($questionScores as $row)<tr><td>{{ $loop->iteration }}</td><td>{{ $row['indicator'] }}</td><td>{{ $row['question'] }}</td><td><strong class="report-average">{{ $row['average'] ?? '-' }}</strong></td><td>{{ $row['total'] }}</td></tr>@empty<tr><td colspan="5"><x-admin.empty-state title="Belum ada nilai pertanyaan" /></td></tr>@endforelse</tbody></table></div>
        </x-admin.panel>
        <x-admin.panel title="Komentar Terbaru" description="Masukan penumpang untuk penilaian driver.">
            <div class="comment-list report-comment-list">@forelse($comments as $comment)<article><strong>{{ $comment['rating']->driver?->full_name }}</strong><span>{{ $comment['question'] }}</span><p>{{ $comment['text'] }}</p></article>@empty<x-admin.empty-state title="Belum ada komentar" />@endforelse</div>
        </x-admin.panel>
    </div>

    <x-admin.panel title="Ranking Driver" description="Urutan berdasarkan rating rata-rata, kemudian jumlah penilaian.">
        <div class="table-wrap"><table class="data-table report-performance-table"><thead><tr><th>Rank</th><th>Driver</th><th>Unit Kerja</th><th>Total Penilaian</th><th>Rating Rata-rata</th><th>Rating 5</th><th>Rating 4</th><th>Rating 3</th><th>Rating 2</th><th>Rating 1</th></tr></thead><tbody>@forelse($data['performance'] as $row)<tr><td><span class="report-rank">{{ $loop->iteration }}</span></td><td><strong>{{ $row['name'] }}</strong></td><td>{{ $row['branch'] }}</td><td>{{ $row['total'] }}</td><td><strong class="report-average">{{ $row['average'] ?? '-' }}</strong></td>@foreach([5,4,3,2,1] as $rating)<td>{{ $row['distribution'][$rating] ?? 0 }}</td>@endforeach</tr>@empty<tr><td colspan="10"><x-admin.empty-state title="Belum ada data performa driver" /></td></tr>@endforelse</tbody></table></div>
    </x-admin.panel>
</x-layouts.admin>
