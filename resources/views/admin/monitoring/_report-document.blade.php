<article class="monitoring-document">
    <header>
        <p>KODE FORMULIR: MON-{{ $report['branch']->code }}-{{ $period->format('Ym') }}</p>
        <h2>MONITORING TENAGA ALIH DAYA DRIVER {{ strtoupper($report['branch']->name) }} {{ strtoupper($period->translatedFormat('F Y')) }}</h2>
        <span>Wilayah Operasional: {{ $report['branch']->regency ?: $report['branch']->address ?: '-' }}</span>
    </header>
    <div class="monitoring-document-table-wrap">
        <table>
            <thead><tr><th>No</th><th>Nama</th><th>Status</th><th>Kendaraan Terakhir</th><th>Kehadiran</th>@foreach($report['questions'] as $question)<th><span>{{ $question->indicator ?: str($question->question)->limit(28) }}</span></th>@endforeach<th>Nilai Akhir</th></tr></thead>
            <tbody>
                @forelse($report['rows'] as $row)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $row['driver']->full_name }}</td><td>{{ $row['driver']->employeeCategory?->name ?? 'Driver' }}</td><td>{{ $row['vehicle']?->police_number ?? '-' }}</td><td>{{ $row['attendance_score'] !== null ? number_format($row['attendance_score'], 1) : '-' }}</td>@foreach($report['questions'] as $question)<td>{{ isset($row['report_scores'][$question->id]) ? number_format($row['report_scores'][$question->id], 1) : '-' }}</td>@endforeach<td><b>{{ $row['final_score'] !== null ? number_format($row['final_score'], 1) : '-' }}</b></td></tr>
                @empty
                    <tr><td colspan="{{ 6 + $report['questions']->count() }}">Belum ada driver aktif pada unit kerja ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <footer><div><strong>Keterangan Nilai</strong><span>SB 91–100 · B 81–90 · C 71–80 · K 51–70 · SK 0–50</span></div><p>Dicetak {{ now(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }} WITA</p></footer>
</article>
