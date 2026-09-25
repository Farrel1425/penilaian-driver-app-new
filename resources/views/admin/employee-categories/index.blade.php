<x-layouts.admin title="Kategori Pegawai">
    <x-slot:pageActions>
        <form class="master-filter-panel" method="GET" action="{{ route('admin.employee-categories.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label><span>STATUS KATEGORI</span><select name="status" onchange="this.form.requestSubmit()" aria-label="Filter status kategori"><option value="">Semua Status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select></label>
            @if (request()->filled('search') || request()->filled('status'))<a class="secondary-button assessment-reset-button" href="{{ route('admin.employee-categories.index') }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>@endif
            <a class="primary-button master-create-button" href="{{ route('admin.employee-categories.create') }}"><x-lucide-plus aria-hidden="true" /><span>Tambah</span></a>
        </form>
    </x-slot:pageActions>

    <section class="branch-list-card master-table-card">
        <div class="table-wrap branch-table-wrap">
            <table class="data-table branch-data-table">
                <thead><tr><th>KATEGORI</th><th>DATA SIM</th><th class="branch-column-count">PEGAWAI</th><th class="branch-column-status">STATUS</th><th class="branch-column-actions">AKSI</th></tr></thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td>{{ $category->requires_sim ? 'Memerlukan SIM' : 'Tidak memerlukan SIM' }}</td>
                            <td class="branch-cell-count">{{ $category->employees_count }}</td>
                            <td class="branch-cell-status"><span class="driver-status driver-status-{{ $category->status }}">{{ $category->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="branch-cell-actions"><div class="table-row-actions"><a href="{{ route('admin.employee-categories.edit', $category) }}" aria-label="Edit kategori {{ $category->name }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.employee-categories.toggle-status', $category) }}" data-confirm data-no-loading data-confirm-title="{{ $category->status === 'active' ? 'Nonaktifkan kategori?' : 'Aktifkan kategori?' }}" data-confirm-description="Kategori nonaktif tidak dapat dipilih pada data pegawai." data-confirm-label="{{ $category->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}" data-confirm-tone="primary" data-confirm-icon="power">@csrf @method('PATCH')<button type="submit" aria-label="Ubah status kategori {{ $category->name }}" title="{{ $category->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}"><x-lucide-power aria-hidden="true" /></button></form><form method="POST" action="{{ route('admin.employee-categories.destroy', $category) }}" data-delete-confirm data-no-loading data-delete-name="Kategori {{ $category->name }}" data-delete-description="Kategori yang masih dipakai pegawai akan dinonaktifkan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus kategori {{ $category->name }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-admin.empty-state title="Belum ada kategori pegawai" description="Tambahkan kategori seperti Driver, Customer Service, atau Satpam." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="branch-pagination"><span>Menampilkan {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</span>@if ($categories->hasPages())<x-admin.pagination :paginator="$categories" label="Pagination kategori pegawai" />@endif</footer>
    </section>
</x-layouts.admin>
