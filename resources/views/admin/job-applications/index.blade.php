<x-layouts.admin title="Data Pelamar">
    <x-slot:pageActions>
        <form class="master-filter-panel" method="GET" action="{{ route('admin.job-applications.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label><span>POSISI</span><select name="vacancy" onchange="this.form.requestSubmit()"><option value="">Semua Posisi</option>@foreach ($vacancies as $vacancy)<option value="{{ $vacancy->id }}" @selected((string) request('vacancy') === (string) $vacancy->id)>{{ $vacancy->title }}</option>@endforeach</select></label>
            <label><span>STATUS</span><select name="status" onchange="this.form.requestSubmit()"><option value="">Semua Status</option>@foreach (App\Models\JobApplication::STATUSES as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
            @if (request()->filled('search') || request()->filled('vacancy') || request()->filled('status'))<a class="secondary-button assessment-reset-button" href="{{ route('admin.job-applications.index') }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>@endif
        </form>
    </x-slot:pageActions>
    <section class="branch-list-card master-table-card">
        <div class="table-wrap">
            <table class="data-table recruitment-admin-table"><thead><tr><th>TANGGAL</th><th>PELAMAR</th><th>POSISI</th><th>CABANG PILIHAN</th><th>KONTAK</th><th>STATUS</th><th>AKSI</th></tr></thead><tbody>
                @forelse ($applications as $application)
                    <tr>
                        <td><strong>{{ $application->created_at->timezone(config('app.display_timezone'))->format('d M Y') }}</strong><small>{{ $application->created_at->timezone(config('app.display_timezone'))->format('H:i') }}</small></td>
                        <td><strong>{{ $application->full_name }}</strong><small>NIK {{ $application->maskedNik() }}</small></td>
                        <td>{{ $application->vacancy->title }}</td><td>{{ $application->branch->name }}</td>
                        <td><span>{{ $application->whatsapp }}</span><small>{{ $application->email }}</small></td>
                        <td><span class="application-status application-status-{{ $application->status }}">{{ $application->statusLabel() }}</span></td>
                        <td><div class="table-row-actions"><a href="{{ route('admin.job-applications.show', $application) }}" aria-label="Lihat pelamar {{ $application->full_name }}" title="Lihat detail"><x-lucide-eye aria-hidden="true" /></a><a href="{{ route('admin.job-applications.document', $application) }}" download data-no-loading aria-label="Unduh PDF {{ $application->full_name }}" title="Unduh PDF"><x-lucide-download aria-hidden="true" /></a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-admin.empty-state title="Belum ada pelamar" description="Lamaran yang dikirim dari halaman Recruitment akan muncul di sini." /></td></tr>
                @endforelse
            </tbody></table>
        </div>
        <footer class="branch-pagination"><span>Menampilkan {{ $applications->firstItem() ?? 0 }} - {{ $applications->lastItem() ?? 0 }} dari {{ $applications->total() }} pelamar</span>@if ($applications->hasPages())<x-admin.pagination :paginator="$applications" label="Pagination pelamar" />@endif</footer>
    </section>
</x-layouts.admin>
