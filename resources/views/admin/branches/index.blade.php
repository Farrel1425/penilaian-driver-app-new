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
                <thead><tr><th>Kode</th><th>Cabang Unit Kerja</th><th>PIC / Kontak</th><th>Driver</th><th>Kendaraan</th><th>Status</th><th class="branch-column-actions">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr>
                            <td><strong>{{ $branch->code }}</strong></td>
                            <td><span>{{ $branch->name }}</span><small>{{ $branch->regency ?: '-' }}</small></td>
                            <td><span>{{ $branch->pic_name ?: '-' }}</span><small>{{ $branch->phone ?: '-' }}{{ $branch->email ? ' - '.$branch->email : '' }}</small></td>
                            <td>{{ $branch->drivers_count }}</td>
                            <td>{{ $branch->vehicles_count }}</td>
                            <td><span class="driver-status driver-status-{{ $branch->status }}">{{ $branch->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><div class="table-row-actions"><a href="{{ route('admin.branches.show', $branch) }}" aria-label="Lihat cabang {{ $branch->name }}" title="Lihat"><x-lucide-eye aria-hidden="true" /></a><a href="{{ route('admin.branches.edit', $branch) }}" aria-label="Edit cabang {{ $branch->name }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" data-delete-confirm data-no-loading data-delete-name="Unit kerja {{ $branch->name }}" data-delete-description="Jika unit kerja ini memiliki data terkait, unit kerja beserta driver dan kendaraannya akan dinonaktifkan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus cabang {{ $branch->name }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
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
                <nav aria-label="Pagination cabang">
                    @if ($branches->onFirstPage())
                        <span class="driver-page-button is-disabled"><x-lucide-chevron-left aria-hidden="true" /></span>
                    @else
                        <a class="driver-page-button" href="{{ $branches->previousPageUrl() }}" aria-label="Halaman sebelumnya"><x-lucide-chevron-left aria-hidden="true" /></a>
                    @endif
                    <span class="driver-page-button is-current">{{ $branches->currentPage() }}</span>
                    @if ($branches->hasMorePages())
                        <a class="driver-page-button" href="{{ $branches->nextPageUrl() }}" aria-label="Halaman berikutnya"><x-lucide-chevron-right aria-hidden="true" /></a>
                    @else
                        <span class="driver-page-button is-disabled"><x-lucide-chevron-right aria-hidden="true" /></span>
                    @endif
                </nav>
            @endif
        </footer>
    </section>
</x-layouts.admin>
