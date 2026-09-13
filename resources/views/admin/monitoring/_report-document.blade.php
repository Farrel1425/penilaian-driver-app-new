<article class="monitoring-document">
    <header>
        <div class="monitoring-document-meta">
            <span>KODE FORMULIR: MON-{{ $report['branch']->code }}-{{ $period->format('Ym') }}</span>
            <span>DOKUMEN MONITORING OPERASIONAL</span>
        </div>
        <h2>MONITORING TENAGA ALIH DAYA DRIVER {{ strtoupper($report['branch']->name) }} {{ strtoupper($period->translatedFormat('F Y')) }}</h2>
        <p>Wilayah Operasional: {{ $report['branch']->name }}</p>
    </header>

    <div class="monitoring-document-table-wrap">
        <table class="monitoring-document-matrix">
            <colgroup>
                <col class="monitoring-col-number">
                <col class="monitoring-col-name">
                <col class="monitoring-col-status">
                <col class="monitoring-col-attendance">
                @foreach($report['questions'] as $question)<col class="monitoring-col-indicator">@endforeach
                <col class="monitoring-col-final">
            </colgroup>
            <thead>
                <tr class="monitoring-category-row">
                    <th rowspan="2" width="4%">No</th>
                    <th rowspan="2" width="22%">Nama</th>
                    <th rowspan="2" width="11%">Status</th>
                    <th width="7%">Sikap Kerja</th>
                    @if($report['questions']->isNotEmpty())
                        <th colspan="{{ $report['questions']->count() }}" width="48%">Kinerja Pelayanan</th>
                    @endif
                    <th rowspan="2" width="8%" class="monitoring-final-heading">Nilai</th>
                </tr>
                <tr class="monitoring-indicator-row">
                    <th><span>Kehadiran / Absen</span></th>
                    @foreach($report['questions'] as $question)
                        <th><span>{{ $question->indicator ?: str($question->question)->limit(42) }}</span></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($report['rows'] as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['driver']->full_name }}</td>
                        <td>{{ $row['driver']->employeeCategory?->name ?? 'Driver' }}</td>
                        <td>{{ $row['attendance_score'] !== null ? round($row['attendance_score'] / 10, 1) : '-' }}</td>
                        @foreach($report['questions'] as $question)
                            <td>{{ isset($row['report_scores'][$question->id]) ? round($row['report_scores'][$question->id], 1) : '-' }}</td>
                        @endforeach
                        <td class="monitoring-final-value">{{ $row['final_score'] !== null ? round($row['final_score'], 1) : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 5 + $report['questions']->count() }}">Belum ada driver aktif pada unit kerja ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @php($completedScores = $report['rows']->pluck('final_score')->filter(fn ($score) => $score !== null))
    @php($averageScore = $completedScores->isNotEmpty() ? round($completedScores->avg(), 1) : null)
    @php($averageGrade = match (true) { $averageScore === null => null, $averageScore >= 91 => 'SB (SANGAT BAIK)', $averageScore >= 81 => 'B (BAIK)', $averageScore >= 71 => 'C (CUKUP)', $averageScore >= 51 => 'K (KURANG)', default => 'SK (SANGAT KURANG)' })

    <section class="monitoring-document-summary">
        <div>
            <table class="monitoring-score-legend">
                <thead><tr><th>Keterangan</th><th>Bobot</th><th>Nilai Rata-rata</th></tr></thead>
                <tbody>
                    <tr><td>SB (Sangat Baik)</td><td>10</td><td>91 - 100</td></tr>
                    <tr class="is-highlighted"><td>B (Baik)</td><td>8</td><td>81 - 90</td></tr>
                    <tr><td>C (Cukup)</td><td>7</td><td>71 - 80</td></tr>
                    <tr><td>K (Kurang)</td><td>5</td><td>51 - 70</td></tr>
                    <tr><td>SK (Sangat Kurang)</td><td>0</td><td>0 - 50</td></tr>
                </tbody>
            </table>
            <p class="monitoring-document-note">
                @if($averageScore !== null)
                    *Catatan: Rata-rata nilai akhir driver bulan ini adalah <strong>{{ $averageScore }}</strong> dan masuk kategori <strong>{{ $averageGrade }}</strong>.
                @else
                    *Catatan: Belum tersedia nilai akhir driver yang lengkap pada periode ini.
                @endif
            </p>
        </div>
    </section>

    <footer>
        <span>Dihasilkan Sistem: MONITORING-{{ $report['branch']->code }} / {{ now(config('app.display_timezone'))->format('YmdHis') }}</span>
        <span>Dicetak {{ now(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }} WITA</span>
    </footer>
</article>
