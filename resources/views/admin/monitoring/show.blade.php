<x-layouts.admin title="Detail Monitoring">
    <x-slot:pageActions>
        <div class="monitoring-detail-actions">
            <a class="secondary-button" href="{{ route('admin.monitoring.index', ['period' => $period->format('Y-m')]) }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a>
            <form method="GET"><label class="native-picker-field"><span>Periode</span><input type="month" name="period" value="{{ $period->format('Y-m') }}" onchange="this.form.requestSubmit()"></label><input type="hidden" name="status" value="{{ $status }}">@if (request()->filled('period') || request()->filled('status'))<a class="secondary-button assessment-reset-button" href="{{ route('admin.monitoring.show', $branch) }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>@endif</form>
            <a class="monitoring-print-button" data-no-loading href="{{ route('admin.monitoring.branch.report', [$branch, 'period' => $period->format('Y-m')]) }}" title="Cetak laporan cabang" aria-label="Cetak laporan cabang"><x-lucide-printer aria-hidden="true" /><span>Cetak</span></a>
        </div>
    </x-slot>

    <div class="monitoring-intro monitoring-detail-intro">
        <h2>Unit Kerja: {{ $branch->name }}</h2>
        <p>{{ $branch->regency ?: 'Wilayah operasional belum tersedia' }} · Periode {{ $period->translatedFormat('F Y') }}</p>
    </div>

    <section class="monitoring-panel">
        <header class="monitoring-panel-header">
            <div><h3>Daftar Personel Driver Cabang</h3><p>Kelola presensi bulanan dan tinjau hasil penilaian driver.</p></div>
            <nav class="monitoring-status-tabs" aria-label="Filter status driver">
                <a @class(['is-active' => !in_array($status, ['complete', 'incomplete'], true)]) href="{{ route('admin.monitoring.show', [$branch, 'period' => $period->format('Y-m')]) }}">Semua</a>
                <a @class(['is-active' => $status === 'complete']) href="{{ route('admin.monitoring.show', [$branch, 'period' => $period->format('Y-m'), 'status' => 'complete']) }}">Lengkap</a>
                <a @class(['is-active' => $status === 'incomplete']) href="{{ route('admin.monitoring.show', [$branch, 'period' => $period->format('Y-m'), 'status' => 'incomplete']) }}">Belum Lengkap</a>
            </nav>
        </header>
        <div class="table-wrap">
            <table class="data-table monitoring-table monitoring-driver-table">
                <thead><tr><th>Driver &amp; Identitas</th><th>Kendaraan dari Penilaian Terbaru</th><th>Rekap H/S/I/A</th><th>Nilai Driver</th><th>Nilai Absensi</th><th>Nilai Akhir</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        @php($driver = $row['driver'])
                        @php($attendance = $row['attendance'])
                        @php($hasDriverPhoto = $driver->photo && (Str::startsWith($driver->photo, ['http://', 'https://', '/']) || Storage::disk('public')->exists($driver->photo)))
                        <tr>
                            <td><div class="monitoring-driver"><span class="monitoring-avatar">@if($hasDriverPhoto)<img src="{{ Str::startsWith($driver->photo, ['http://', 'https://', '/']) ? $driver->photo : asset('storage/'.$driver->photo) }}" alt="Foto {{ $driver->full_name }}">@else{{ Str::upper(Str::substr($driver->full_name, 0, 1)) }}@endif</span><span><strong>{{ $driver->full_name }}</strong><small>NIK: DRV-{{ $branch->code }}-{{ str_pad((string) $driver->id, 3, '0', STR_PAD_LEFT) }}</small><small>{{ $driver->sim_type ? 'SIM '.$driver->sim_type : 'Data SIM tidak tersedia' }}</small></span></div></td>
                            <td>@if($row['vehicle'])<strong>{{ trim($row['vehicle']->brand.' '.$row['vehicle']->model) }}</strong><small>{{ $row['vehicle']->police_number }}</small>@else<span class="monitoring-muted">Belum ada penilaian</span>@endif</td>
                            <td>@if($attendance)<span class="monitoring-attendance-recap"><b>{{ $attendance->present_days }}</b>/<b>{{ $attendance->sick_days }}</b>/<b>{{ $attendance->permitted_days }}</b>/<b>{{ $attendance->absent_days }}</b></span>@else-@endif</td>
                            <td><strong class="monitoring-score">{{ $row['driver_score'] !== null ? number_format($row['driver_score'], 1) : '-' }}</strong><small>{{ $row['rating_count'] }} penilaian</small></td>
                            <td><strong class="monitoring-score">{{ $row['attendance_score'] !== null ? number_format($row['attendance_score'], 1) : '-' }}</strong></td>
                            <td><strong class="monitoring-final-score">{{ $row['final_score'] !== null ? number_format($row['final_score'], 1) : '-' }}</strong></td>
                            <td><span @class(['monitoring-completeness', 'is-complete' => $row['is_complete'], 'is-incomplete' => !$row['is_complete']])>{{ $row['is_complete'] ? 'Lengkap' : 'Belum Lengkap' }}</span></td>
                            <td><div class="monitoring-row-actions"><button class="monitoring-update-button" type="button" data-attendance-open data-driver-id="{{ $driver->id }}" data-action="{{ route('admin.monitoring.attendance.store', [$branch, $driver]) }}" data-driver="{{ $driver->full_name }}" data-identity="{{ 'DRV-'.$branch->code.'-'.str_pad((string) $driver->id, 3, '0', STR_PAD_LEFT) }}" data-present="{{ (string) old('attendance_driver_id') === (string) $driver->id ? old('present_days', 0) : ($attendance?->present_days ?? 0) }}" data-sick="{{ (string) old('attendance_driver_id') === (string) $driver->id ? old('sick_days', 0) : ($attendance?->sick_days ?? 0) }}" data-permitted="{{ (string) old('attendance_driver_id') === (string) $driver->id ? old('permitted_days', 0) : ($attendance?->permitted_days ?? 0) }}" data-absent="{{ (string) old('attendance_driver_id') === (string) $driver->id ? old('absent_days', 0) : ($attendance?->absent_days ?? 0) }}">{{ $attendance ? 'Update Absensi' : 'Input Absensi' }}</button><a class="monitoring-icon-button" href="{{ route('admin.monitoring.driver', [$branch, $driver, 'period' => $period->format('Y-m')]) }}" title="Preview penilaian" aria-label="Preview penilaian {{ $driver->full_name }}"><x-lucide-eye aria-hidden="true" /></a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-admin.empty-state title="Belum ada driver" description="Tidak ada driver aktif yang sesuai dengan filter ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="monitoring-panel-footer"><span>Menampilkan {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} driver</span>@if($rows->hasPages())<x-admin.pagination :paginator="$rows" label="Pagination driver monitoring" />@endif</footer>
    </section>

    <div class="monitoring-modal" data-attendance-modal data-attendance-max-days="{{ $workingDays }}" data-error-driver-id="{{ old('attendance_driver_id') }}" hidden>
        <button class="monitoring-modal-backdrop" type="button" data-attendance-close aria-label="Tutup modal"></button>
        <section class="monitoring-modal-card" role="dialog" aria-modal="true" aria-labelledby="attendance-modal-title">
            <header><span class="monitoring-modal-icon"><x-lucide-clipboard-check aria-hidden="true" /></span><div><h3 id="attendance-modal-title">Input &amp; Validasi Absensi Driver</h3><p>Rekap kehadiran bulanan dan kalkulasi skor kedisiplinan</p></div><button class="monitoring-modal-close" type="button" data-attendance-close aria-label="Tutup"><x-lucide-x aria-hidden="true" /></button></header>
            <div class="monitoring-modal-context"><strong data-attendance-driver>Driver</strong><span data-attendance-identity></span><b>Periode {{ $period->translatedFormat('F Y') }}</b></div>
            <form method="POST" data-attendance-form>
                @csrf
                <input type="hidden" name="period" value="{{ $period->format('Y-m') }}">
                <input type="hidden" name="attendance_driver_id" value="{{ old('attendance_driver_id') }}">
                <div class="monitoring-attendance-grid">
                    <label><span>Hadir</span><input type="number" min="0" max="{{ $workingDays }}" name="present_days" value="0" required><small>Bobot 100%</small></label>
                    <label><span>Sakit</span><input type="number" min="0" max="{{ $workingDays }}" name="sick_days" value="0" required><small>Bobot 75%</small></label>
                    <label><span>Izin</span><input type="number" min="0" max="{{ $workingDays }}" name="permitted_days" value="0" required><small>Bobot 50%</small></label>
                    <label><span>Alpha</span><input type="number" min="0" max="{{ $workingDays }}" name="absent_days" value="0" required><small>Bobot 0%</small></label>
                </div>
                <div class="monitoring-score-preview"><span>Perkiraan Nilai Absensi</span><strong data-attendance-score>0.0</strong><small>Total hari: <b data-attendance-total>0</b> dari {{ $workingDays }} hari kerja</small></div>
                @if($errors->any())<div class="monitoring-form-error">{{ $errors->first() }}</div>@endif
                <footer><button class="secondary-button" type="button" data-attendance-close>Batal</button><button class="primary-button" type="submit"><x-lucide-save aria-hidden="true" /> Simpan Absensi</button></footer>
            </form>
        </section>
    </div>
</x-layouts.admin>
