<x-layouts.admin title="Log Aktivitas">
    <x-slot:pageActions>
        <form class="activity-log-filter admin-dashboard-filters" method="GET" action="{{ route('admin.activity-logs.index') }}" data-auto-filter-form>
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label><span>Mulai</span><input type="date" name="start_date" value="{{ request('start_date') }}" aria-label="Tanggal mulai"></label>
            <label><span>Sampai</span><input type="date" name="end_date" value="{{ request('end_date') }}" aria-label="Tanggal akhir"></label>
            <label><span>Admin</span><select name="user_id" aria-label="Filter admin">
                <option value="">Semua Admin</option>
                @foreach ($admins as $admin)
                    <option value="{{ $admin->id }}" @selected((string) request('user_id') === (string) $admin->id)>{{ $admin->name }}</option>
                @endforeach
            </select></label>
            <a class="secondary-button assessment-reset-button" href="{{ route('admin.activity-logs.index') }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>
            <a class="primary-button activity-log-export" data-no-loading href="{{ route('admin.activity-logs.export', request()->query()) }}" title="Export Excel" aria-label="Export Excel"><x-lucide-download aria-hidden="true" /><span>Export</span></a>
        </form>
    </x-slot>

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
                <x-admin.pagination :paginator="$logs" label="Pagination log aktivitas" />
            @endif
        </footer>
    </x-admin.panel>
</x-layouts.admin>
