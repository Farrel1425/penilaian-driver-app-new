<x-layouts.admin title="Master Cabang">
    <section class="branch-list-card">
        <form class="branch-list-toolbar" method="GET" action="{{ route('admin.branches.index') }}" data-debounced-search-form>
            <div class="branch-list-filters">
                <label class="branch-search-field">
                    <x-lucide-search aria-hidden="true" />
                    <input name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, PIC, atau kontak..." aria-label="Cari cabang" data-debounced-search>
                </label>
                <select name="status" onchange="this.form.requestSubmit()" aria-label="Filter status">
                    <option value="">Semua Status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <a class="primary-button branch-create-button" href="{{ route('admin.branches.create') }}"><x-lucide-plus aria-hidden="true" /><span>Tambah Cabang</span></a>
        </form>

        <div class="table-wrap branch-table-wrap">
            <table class="data-table branch-data-table">
                <colgroup>
                    <col class="branch-col-code">
                    <col class="branch-col-name">
                    <col class="branch-col-contact">
                    <col class="branch-col-driver-count">
                    <col class="branch-col-vehicle-count">
                    <col class="branch-col-status">
                    <col class="branch-col-actions">
                </colgroup>
                <thead><tr><th>Kode</th><th>Cabang Unit Kerja</th><th>PIC / Kontak</th><th class="branch-column-count">Driver</th><th class="branch-column-count">Kendaraan</th><th class="branch-column-status">Status</th><th class="branch-column-actions">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr>
                            <td><strong>{{ $branch->code }}</strong></td>
                            <td><span>{{ $branch->name }}</span><small>{{ $branch->regency ?: '-' }}</small></td>
                            <td><span>{{ $branch->pic_name ?: '-' }}</span><small>{{ $branch->phone ?: '-' }}{{ $branch->email ? ' - '.$branch->email : '' }}</small></td>
                            <td class="branch-cell-count">{{ $branch->drivers_count }}</td>
                            <td class="branch-cell-count">{{ $branch->vehicles_count }}</td>
                            <td class="branch-cell-status"><span class="driver-status driver-status-{{ $branch->status }}">{{ $branch->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="branch-cell-actions"><div class="table-row-actions"><a href="{{ route('admin.branches.show', $branch) }}" aria-label="Lihat cabang {{ $branch->name }}" title="Lihat"><x-lucide-eye aria-hidden="true" /></a><a href="{{ route('admin.branches.edit', $branch) }}" aria-label="Edit cabang {{ $branch->name }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" data-delete-confirm data-no-loading data-delete-name="Unit kerja {{ $branch->name }}" data-delete-description="Jika unit kerja ini memiliki data terkait, unit kerja beserta driver dan kendaraannya akan dinonaktifkan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus cabang {{ $branch->name }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-admin.empty-state title="Belum ada cabang" description="Tambahkan cabang untuk mulai mengelola driver dan kendaraan." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="branch-pagination">
            <span>Menampilkan {{ $branches->firstItem() ?? 0 }} - {{ $branches->lastItem() ?? 0 }} dari {{ $branches->total() }} data</span>
            @if ($branches->hasPages())
                <x-admin.pagination :paginator="$branches" label="Pagination cabang" />
            @endif
        </footer>
    </section>
</x-layouts.admin>
