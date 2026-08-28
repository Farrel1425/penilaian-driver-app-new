<x-layouts.admin title="Log Aktivitas">
    <x-admin.panel class="activity-log-toolbar">
        <form class="activity-log-filter" method="GET" action="{{ route('admin.activity-logs.index') }}">
            <label class="activity-log-search-field">
                <x-lucide-search aria-hidden="true" />
                <input name="search" value="{{ request('search') }}" placeholder="Cari aktivitas, modul, atau admin">
            </label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" aria-label="Tanggal mulai">
            <input type="date" name="end_date" value="{{ request('end_date') }}" aria-label="Tanggal akhir">
            <select name="user_id" aria-label="Filter admin">
                <option value="">Semua Admin</option>
                @foreach ($admins as $admin)
                    <option value="{{ $admin->id }}" @selected((string) request('user_id') === (string) $admin->id)>{{ $admin->name }}</option>
                @endforeach
            </select>
            <button class="secondary-button" type="submit"><x-lucide-filter aria-hidden="true" /><span>Terapkan</span></button>
            <a class="secondary-button" href="{{ route('admin.activity-logs.index') }}"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>
            <a class="primary-button activity-log-export" data-no-loading href="{{ route('admin.activity-logs.export', request()->query()) }}"><x-lucide-download aria-hidden="true" /><span>Export Excel</span></a>
        </form>
    </x-admin.panel>

    <x-admin.panel class="activity-log-table-panel">
        <div class="table-wrap">
            <table class="data-table activity-log-table">
                <thead><tr><th>Tanggal & Jam</th><th>Admin</th><th>Modul</th><th>Aktivitas</th><th>Keterangan</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->timezone(config('app.display_timezone'))?->format('d M Y, H:i') }}</td>
                            <td><strong>{{ $log->user?->name ?? 'Sistem' }}</strong></td>
                            <td><span class="activity-module-badge">{{ $log->module }}</span></td>
                            <td><span class="activity-action-badge">{{ $log->action }}</span></td>
                            <td>{{ $log->description }}</td>
                            <td class="activity-log-ip">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty-state title="Belum ada aktivitas" description="Aktivitas administrator akan tercatat secara otomatis di halaman ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="activity-log-pagination">
            <span>Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} aktivitas</span>
            @if ($logs->hasPages())
                <nav aria-label="Pagination log aktivitas">
                    @if ($logs->onFirstPage())
                        <span class="activity-log-page-button is-disabled"><x-lucide-chevron-left aria-hidden="true" /></span>
                    @else
                        <a class="activity-log-page-button" href="{{ $logs->previousPageUrl() }}" aria-label="Halaman sebelumnya"><x-lucide-chevron-left aria-hidden="true" /></a>
                    @endif
                    <span class="activity-log-page-button is-current" aria-current="page">{{ $logs->currentPage() }}</span>
                    @if ($logs->hasMorePages())
                        <a class="activity-log-page-button" href="{{ $logs->nextPageUrl() }}" aria-label="Halaman berikutnya"><x-lucide-chevron-right aria-hidden="true" /></a>
                    @else
                        <span class="activity-log-page-button is-disabled"><x-lucide-chevron-right aria-hidden="true" /></span>
                    @endif
                </nav>
            @endif
        </footer>
    </x-admin.panel>
</x-layouts.admin>
