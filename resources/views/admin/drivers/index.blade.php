<x-layouts.admin title="Master Driver">
    <section class="driver-list-card">
        <form class="driver-list-toolbar" method="GET" action="{{ route('admin.drivers.index') }}" data-debounced-search-form>
            <div class="driver-list-filters">
                <label class="driver-search-field">
                    <x-lucide-search aria-hidden="true" />
                    <input name="search" value="{{ request('search') }}" placeholder="Cari nama driver, no. SIM..." aria-label="Cari driver" data-debounced-search>
                </label>

                <select name="branch_id" onchange="this.form.requestSubmit()" aria-label="Filter unit kerja">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) request('branch_id') === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.requestSubmit()" aria-label="Filter status">
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <a class="primary-button driver-create-button" href="{{ route('admin.drivers.create') }}"><x-lucide-plus aria-hidden="true" /><span>Tambah Driver</span></a>
        </form>

        <div class="table-wrap driver-table-wrap">
            <table class="data-table driver-data-table">
                <thead>
                    <tr><th class="driver-column-number">No</th><th class="driver-column-photo">Foto</th><th>Nama Driver</th><th>No. SIM</th><th>Unit Kerja</th><th>No. HP</th><th>Status</th><th class="driver-column-actions">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                        <tr>
                            <td class="driver-cell-center">{{ $drivers->firstItem() + $loop->index }}</td>
                            <td><div class="driver-photo-thumbnail">@if($driver->photo)<img src="{{ Str::startsWith($driver->photo, ['http://', 'https://', '/']) ? $driver->photo : asset('storage/' . $driver->photo) }}" alt="{{ $driver->full_name }}">@else<span>{{ strtoupper(substr($driver->full_name, 0, 1)) }}</span>@endif</div></td>
                            <td class="driver-cell-name"><strong>{{ $driver->full_name }}</strong><small>{{ $driver->nickname ?: 'Driver operasional' }}</small></td>
                            <td>{{ $driver->sim_number ?: '-' }}</td>
                            <td>{{ $driver->branch?->name ?: '-' }}</td>
                            <td>{{ $driver->phone ?: '-' }}</td>
                            <td><span class="driver-status driver-status-{{ $driver->status }}">{{ $driver->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><div class="table-row-actions"><a href="{{ route('admin.drivers.show', $driver) }}" aria-label="Lihat driver {{ $driver->full_name }}" title="Lihat"><x-lucide-eye aria-hidden="true" /></a><a href="{{ route('admin.drivers.edit', $driver) }}" aria-label="Edit driver {{ $driver->full_name }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.drivers.destroy', $driver) }}" data-delete-confirm data-no-loading data-delete-name="Driver {{ $driver->full_name }}" data-delete-description="Driver yang sudah memiliki penilaian akan dinonaktifkan agar riwayat penilaian tetap tersimpan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus driver {{ $driver->full_name }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-admin.empty-state title="Belum ada driver" description="Tambahkan driver setelah cabang tersedia." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="driver-pagination">
            <span>Menampilkan {{ $drivers->firstItem() ?? 0 }} - {{ $drivers->lastItem() ?? 0 }} dari {{ $drivers->total() }} data</span>
            @if($drivers->hasPages())
                <nav aria-label="Pagination driver">
                    @if($drivers->onFirstPage())<span class="driver-page-button is-disabled"><x-lucide-chevron-left aria-hidden="true" /></span>@else<a class="driver-page-button" href="{{ $drivers->previousPageUrl() }}" aria-label="Halaman sebelumnya"><x-lucide-chevron-left aria-hidden="true" /></a>@endif
                    <span class="driver-page-button is-current">{{ $drivers->currentPage() }}</span>
                    @if($drivers->hasMorePages())<a class="driver-page-button" href="{{ $drivers->nextPageUrl() }}" aria-label="Halaman berikutnya"><x-lucide-chevron-right aria-hidden="true" /></a>@else<span class="driver-page-button is-disabled"><x-lucide-chevron-right aria-hidden="true" /></span>@endif
                </nav>
            @endif
        </footer>
    </section>
</x-layouts.admin>
