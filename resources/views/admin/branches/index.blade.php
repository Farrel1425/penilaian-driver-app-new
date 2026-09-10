<x-layouts.admin title="Master Cabang">
    <x-slot:pageActions>
        <form class="master-filter-panel" method="GET" action="{{ route('admin.branches.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label><span>STATUS UNIT KERJA</span><select name="status" onchange="this.form.requestSubmit()" aria-label="Filter status"><option value="">Semua Status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select></label>
            @if (request()->filled('search') || request()->filled('status'))<a class="secondary-button assessment-reset-button" href="{{ route('admin.branches.index') }}"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>@endif
            <a class="primary-button master-create-button" href="{{ route('admin.branches.create') }}"><x-lucide-plus aria-hidden="true" /><span>Tambah Unit Kerja</span></a>
        </form>
    </x-slot:pageActions>

    <section class="branch-list-card master-table-card">
        <div class="table-wrap branch-table-wrap">
            <table class="data-table branch-data-table">
                <colgroup><col class="branch-col-code"><col class="branch-col-name"><col class="branch-col-contact"><col class="branch-col-driver-count"><col class="branch-col-vehicle-count"><col class="branch-col-status"><col class="branch-col-actions"></colgroup>
                <thead><tr><th>KODE</th><th>UNIT KERJA</th><th>PIC / KONTAK</th><th class="branch-column-count">DRIVER</th><th class="branch-column-count">KENDARAAN</th><th class="branch-column-status">STATUS</th><th class="branch-column-actions">AKSI</th></tr></thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr><td><strong>{{ $branch->code }}</strong></td><td><span>{{ $branch->name }}</span><small>{{ $branch->regency ?: '-' }}</small></td><td><span>{{ $branch->pic_name ?: '-' }}</span><small>{{ $branch->phone ?: '-' }}{{ $branch->email ? ' - '.$branch->email : '' }}</small></td><td class="branch-cell-count">{{ $branch->drivers_count }}</td><td class="branch-cell-count">{{ $branch->vehicles_count }}</td><td class="branch-cell-status"><span class="driver-status driver-status-{{ $branch->status }}">{{ $branch->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td><td class="branch-cell-actions"><div class="table-row-actions"><a href="{{ route('admin.branches.show', $branch) }}" aria-label="Lihat Unit Kerja {{ $branch->name }}" title="Lihat detail"><x-lucide-eye aria-hidden="true" /></a><a href="{{ route('admin.branches.edit', $branch) }}" aria-label="Edit Unit Kerja {{ $branch->name }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" data-delete-confirm data-no-loading data-delete-name="Unit kerja {{ $branch->name }}" data-delete-description="Jika unit kerja ini memiliki data terkait, unit kerja beserta driver dan kendaraannya akan dinonaktifkan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus Unit Kerja {{ $branch->name }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td></tr>
                    @empty
                        <tr><td colspan="7"><x-admin.empty-state title="Belum ada Unit Kerja" description="Tambahkan Unit Kerja untuk mulai mengelola pegawai dan kendaraan." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="branch-pagination"><span>Menampilkan {{ $branches->firstItem() ?? 0 }} - {{ $branches->lastItem() ?? 0 }} dari {{ $branches->total() }} data</span>@if ($branches->hasPages())<x-admin.pagination :paginator="$branches" label="Pagination Unit Kerja" />@endif</footer>
    </section>
</x-layouts.admin>
