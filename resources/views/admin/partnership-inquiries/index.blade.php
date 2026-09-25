<x-layouts.admin title="Permintaan Kerjasama">
    <x-slot:pageActions>
        <form class="admin-dashboard-filters" method="GET" action="{{ route('admin.partnership-inquiries.index') }}" data-dashboard-filter-form>
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label>
                <span>Mulai</span>
                <input type="date" name="start_date" value="{{ request('start_date') }}" max="{{ request('end_date') ?: now()->toDateString() }}" data-dashboard-filter>
            </label>
            <label>
                <span>Sampai</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" min="{{ request('start_date') }}" max="{{ now()->toDateString() }}" data-dashboard-filter>
            </label>
            <label>
                <span>Status</span>
                <select name="status" data-dashboard-filter aria-label="Filter status permintaan">
                    <option value="">Semua Status</option>
                    @foreach (\App\Models\PartnershipInquiry::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            @if (request()->filled('search') || request()->filled('start_date') || request()->filled('end_date') || request()->filled('status'))
                <a class="secondary-button assessment-reset-button dashboard-filter-reset" href="{{ route('admin.partnership-inquiries.index') }}" title="Reset filter" aria-label="Reset filter">
                    <x-lucide-rotate-ccw aria-hidden="true" />
                    <span>Reset</span>
                </a>
            @endif
        </form>
    </x-slot:pageActions>

    <section class="branch-list-card master-table-card inquiry-list-card">
        <div class="table-wrap">
            <table class="data-table inquiry-data-table">
                <thead><tr><th>TANGGAL</th><th>PERUSAHAAN / INSTANSI</th><th>PIC</th><th>KONTAK</th><th>LAYANAN</th><th>STATUS</th><th>AKSI</th></tr></thead>
                <tbody>
                    @forelse ($inquiries as $inquiry)
                        <tr>
                            <td><strong>{{ $inquiry->created_at->timezone(config('app.display_timezone'))->format('d M Y') }}</strong><small>{{ $inquiry->created_at->timezone(config('app.display_timezone'))->format('H:i') }}</small></td>
                            <td><strong>{{ $inquiry->company_name }}</strong></td>
                            <td>{{ $inquiry->contact_name }}</td>
                            <td><span>{{ $inquiry->whatsapp }}</span><small>{{ $inquiry->email }}</small></td>
                            <td>{{ $inquiry->service }}</td>
                            <td><span class="inquiry-status inquiry-status-{{ $inquiry->status }}">{{ $inquiry->statusLabel() }}</span></td>
                            <td><div class="table-row-actions"><a href="{{ route('admin.partnership-inquiries.show', $inquiry) }}" aria-label="Lihat permintaan dari {{ $inquiry->company_name }}" title="Lihat detail"><x-lucide-eye aria-hidden="true" /></a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-admin.empty-state title="Belum ada permintaan" description="Permintaan yang dikirim melalui landing page akan muncul di sini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="branch-pagination"><span>Menampilkan {{ $inquiries->firstItem() ?? 0 }} - {{ $inquiries->lastItem() ?? 0 }} dari {{ $inquiries->total() }} data</span>@if ($inquiries->hasPages())<x-admin.pagination :paginator="$inquiries" label="Pagination Permintaan Kerjasama" />@endif</footer>
    </section>
</x-layouts.admin>
