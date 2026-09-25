<x-layouts.admin title="Lowongan Recruitment">
    <x-slot:pageActions>
        <form class="master-filter-panel" method="GET" action="{{ route('admin.job-vacancies.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label><span>PERIODE</span><select name="period" onchange="this.form.requestSubmit()"><option value="">Semua Periode</option>@foreach ($periods as $period)<option value="{{ $period->id }}" @selected((string) request('period') === (string) $period->id)>{{ $period->name }}</option>@endforeach</select></label>
            <label><span>STATUS</span><select name="status" onchange="this.form.requestSubmit()"><option value="">Semua Status</option>@foreach (App\Models\JobVacancy::STATUSES as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
            @if (request()->filled('search') || request()->filled('period') || request()->filled('status'))<a class="secondary-button assessment-reset-button" href="{{ route('admin.job-vacancies.index') }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>@endif
            <a class="primary-button master-create-button" href="{{ route('admin.job-vacancies.create') }}" title="Tambah lowongan" aria-label="Tambah lowongan"><x-lucide-plus aria-hidden="true" /><span>Tambah</span></a>
        </form>
    </x-slot:pageActions>
    <section class="branch-list-card master-table-card">
        <div class="table-wrap">
            <table class="data-table recruitment-admin-table"><thead><tr><th>POSISI</th><th>PERIODE</th><th>CABANG</th><th>FORMASI</th><th>PELAMAR</th><th>STATUS</th><th>AKSI</th></tr></thead><tbody>
                @forelse ($vacancies as $vacancy)
                    <tr>
                        <td><strong>{{ $vacancy->title }}</strong><small>{{ $vacancy->category }} &bull; {{ $vacancy->work_type }}</small></td>
                        <td>{{ $vacancy->period->name }}</td>
                        <td><span class="recruitment-table-branches">{{ $vacancy->branches->pluck('name')->join(', ') }}</span></td>
                        <td>{{ $vacancy->quota }}</td><td>{{ $vacancy->applications_count }}</td>
                        <td><span class="recruitment-status recruitment-status-{{ $vacancy->status }}">{{ $vacancy->statusLabel() }}</span></td>
                        <td><div class="table-row-actions"><a href="{{ route('admin.job-vacancies.edit', $vacancy) }}" aria-label="Edit lowongan {{ $vacancy->title }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.job-vacancies.destroy', $vacancy) }}" data-delete-confirm data-no-loading data-delete-name="Lowongan {{ $vacancy->title }}" data-delete-description="Lowongan yang sudah memiliki pelamar akan ditutup agar riwayat tetap tersimpan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus lowongan {{ $vacancy->title }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-admin.empty-state title="Belum ada lowongan" description="Buat lowongan dan hubungkan dengan periode serta cabang penempatan." /></td></tr>
                @endforelse
            </tbody></table>
        </div>
        <footer class="branch-pagination"><span>Menampilkan {{ $vacancies->firstItem() ?? 0 }} - {{ $vacancies->lastItem() ?? 0 }} dari {{ $vacancies->total() }} lowongan</span>@if ($vacancies->hasPages())<x-admin.pagination :paginator="$vacancies" label="Pagination lowongan" />@endif</footer>
    </section>
</x-layouts.admin>
