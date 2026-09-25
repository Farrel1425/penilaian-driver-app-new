<x-layouts.admin title="Preview Penilaian">
    <x-slot:pageActions><div class="monitoring-report-actions"><a class="secondary-button" href="{{ route('admin.monitoring.show', [$branch, 'period' => $period->format('Y-m'), 'target' => 'driver']) }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a><a class="primary-button" data-no-loading href="{{ route('admin.monitoring.driver.export', [$branch, $driver, 'period' => $period->format('Y-m')]) }}"><x-lucide-download aria-hidden="true" /><span>Download</span></a></div></x-slot>

    @php
        $grade = match (true) {
            $final_score === null => ['-', 'Belum Lengkap'],
            $final_score >= 91 => ['SB', 'Sangat Baik'],
            $final_score >= 81 => ['B', 'Baik'],
            $final_score >= 71 => ['C', 'Cukup'],
            $final_score >= 51 => ['K', 'Kurang'],
            default => ['SK', 'Sangat Kurang'],
        };
    @endphp

    <div class="monitoring-intro monitoring-preview-intro"><h2>Preview Penilaian</h2><p>Monitoring tenaga alih daya driver {{ $branch->name }} · Periode {{ $period->translatedFormat('F Y') }}</p></div>

    <section class="monitoring-profile-card">
        <div class="monitoring-profile-person">
            <span class="monitoring-profile-photo"><x-entity-photo type="driver" :src="$driver->photo" alt="Foto {{ $driver->full_name }}" /></span>
            <div><span class="monitoring-profile-name"><strong>{{ $driver->full_name }}</strong><b>{{ $driver->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</b></span><p>NIK: DRV-{{ $branch->code }}-{{ str_pad((string) $driver->id, 3, '0', STR_PAD_LEFT) }} · {{ $driver->employeeCategory?->name ?? 'Driver' }}</p><div class="monitoring-profile-tags"><span><x-lucide-car aria-hidden="true" />{{ $vehicle ? trim($vehicle->brand.' '.$vehicle->model).' ('.$vehicle->police_number.')' : 'Belum ada kendaraan yang dinilai' }}</span><span><x-lucide-map-pin aria-hidden="true" />{{ $branch->name }}</span></div></div>
        </div>
        <div class="monitoring-profile-metrics">
            <div><strong>{{ $final_score !== null ? number_format($final_score, 1) : '-' }}</strong><span>Nilai Akhir</span></div>
            <p><b>Kategori: {{ $grade[0] }} ({{ strtoupper($grade[1]) }})</b><span>Driver 90% + Absensi 10%</span></p>
        </div>
        <div class="monitoring-profile-breakdown"><p><span>Nilai Driver</span><strong>{{ $driver_score !== null ? number_format($driver_score, 1) : '-' }}</strong></p><p><span>Nilai Absensi</span><strong>{{ $attendance_score !== null ? number_format($attendance_score, 1) : '-' }}</strong></p><p><span>Nilai Kendaraan</span><strong>{{ $vehicle_score !== null ? number_format($vehicle_score, 1) : '-' }}</strong></p></div>
    </section>

    <div class="monitoring-preview-layout">
        <main>
            <section class="monitoring-evaluation-section">
                <header><div><h3>Penilaian Driver</h3><p>Rata-rata dari {{ $rating_count }} penilaian penumpang pada periode ini</p></div><span>Bobot 100%</span></header>
                <div class="monitoring-indicator-list">
                    @forelse($question_breakdown as $item)
                        <article>
                            <div class="monitoring-indicator-heading"><span>{{ $loop->iteration }}</span><div><strong>{{ $item['question']->indicator ?: 'Penilaian Driver' }}</strong><small>{{ str($item['question']->question)->limit(105) }}</small></div><b>{{ number_format($item['average'], 1) }}/5</b></div>
                            <div class="monitoring-progress"><i style="width: {{ $item['percentage'] }}%"></i></div>
                            <footer><span>Persentase {{ number_format($item['percentage'], 1) }}%</span><span>Kontribusi {{ number_format($item['contribution'], 1) }}/{{ $item['weight'] }}</span></footer>
                        </article>
                    @empty
                        <x-admin.empty-state title="Belum ada penilaian driver" description="Penilaian akan muncul setelah penumpang mengirim formulir pada periode ini." />
                    @endforelse
                </div>
            </section>

            <section class="monitoring-attendance-card">
                <header><div><h3>Rekap Absensi</h3><p>Data kehadiran yang dimasukkan manual oleh administrator</p></div><strong>{{ $attendance_score !== null ? number_format($attendance_score, 1).'%' : '-' }}</strong></header>
                @if($attendance)
                    <div><span><b>{{ $attendance->present_days }}</b>Hadir</span><span><b>{{ $attendance->sick_days }}</b>Sakit</span><span><b>{{ $attendance->permitted_days }}</b>Izin</span><span><b>{{ $attendance->absent_days }}</b>Alpha</span></div>
                @else
                    <p class="monitoring-muted">Absensi periode ini belum diinput.</p>
                @endif
            </section>
        </main>

        <aside>
            <section class="monitoring-comparison-card"><h3>Ringkasan Nilai</h3><div><p><span>Nilai Driver</span><b>{{ $driver_score !== null ? number_format($driver_score, 1) : '-' }}</b></p><i><b style="width: {{ $driver_score ?? 0 }}%"></b></i><p><span>Nilai Absensi</span><b>{{ $attendance_score !== null ? number_format($attendance_score, 1) : '-' }}</b></p><i><b style="width: {{ $attendance_score ?? 0 }}%"></b></i><p><span>Nilai Akhir Monitoring</span><b>{{ $final_score !== null ? number_format($final_score, 1) : '-' }}</b></p><i class="is-highlight"><b style="width: {{ $final_score ?? 0 }}%"></b></i></div></section>
            <section class="monitoring-comments-card"><h3>Umpan Balik Penumpang</h3>@forelse($comments->take(5) as $answer)<article><strong>{{ $answer->question?->indicator ?: 'Catatan Penumpang' }}</strong><p>{{ $answer->answer_text }}</p></article>@empty<p class="monitoring-muted">Belum ada komentar pada periode ini.</p>@endforelse</section>
            <section class="monitoring-grade-card"><h3>Pedoman Nilai</h3><div><span><b>SB</b>91–100</span><span><b>B</b>81–90</span><span><b>C</b>71–80</span><span><b>K</b>51–70</span><span><b>SK</b>0–50</span></div></section>
        </aside>
    </div>
</x-layouts.admin>
