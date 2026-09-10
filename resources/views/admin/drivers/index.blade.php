<x-layouts.admin title="Master Pegawai">
    <x-slot:pageActions>
        <form class="master-filter-panel" method="GET" action="{{ route('admin.employees.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label>
                <span>UNIT KERJA</span>
                <select name="branch_id" onchange="this.form.requestSubmit()" aria-label="Filter unit kerja">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) request('branch_id') === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>KATEGORI PEGAWAI</span>
                <select name="employee_category_id" onchange="this.form.requestSubmit()" aria-label="Filter kategori pegawai">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('employee_category_id') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>STATUS PEGAWAI</span>
                <select name="status" onchange="this.form.requestSubmit()" aria-label="Filter status pegawai">
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
            </label>
            @if (request()->filled('search') || request()->filled('branch_id') || request()->filled('employee_category_id') || request()->filled('status'))
                <a class="secondary-button assessment-reset-button" href="{{ route('admin.employees.index') }}"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>
            @endif
            <a class="primary-button master-create-button" href="{{ route('admin.employees.create') }}"><x-lucide-plus aria-hidden="true" /><span>Tambah Pegawai</span></a>
        </form>
    </x-slot:pageActions>

    <section class="driver-overview">
        <section class="driver-list-card driver-table-card">
            <div class="table-wrap driver-table-wrap">
                <table class="data-table driver-data-table">
                    <colgroup>
                        <col class="driver-col-name">
                        <col class="driver-col-sim">
                        <col class="driver-col-sim">
                        <col class="driver-col-branch">
                        <col class="driver-col-phone">
                        <col class="driver-col-status">
                        <col class="driver-col-actions">
                    </colgroup>
                    <thead>
                        <tr><th>PEGAWAI</th><th>KATEGORI</th><th>NO. SIM</th><th>UNIT KERJA</th><th>NO. HP</th><th class="driver-column-status">STATUS</th><th class="driver-column-actions">AKSI</th></tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            <tr>
                                <td class="driver-cell-profile"><div class="driver-photo-thumbnail">@if($driver->photo)<img src="{{ Str::startsWith($driver->photo, ['http://', 'https://', '/']) ? $driver->photo : asset('storage/' . $driver->photo) }}" alt="{{ $driver->full_name }}">@else<span>{{ strtoupper(substr($driver->full_name, 0, 1)) }}</span>@endif</div><div class="driver-cell-name"><strong>{{ $driver->full_name }}</strong><small>{{ $driver->nickname ?: 'Pegawai' }}</small></div></td>
                                <td>{{ $driver->employeeCategory?->name ?: '-' }}</td>
                                <td>{{ $driver->sim_number ?: '-' }}</td>
                                <td>{{ $driver->branch?->name ?: '-' }}</td>
                                <td>{{ $driver->phone ?: '-' }}</td>
                                <td class="driver-cell-status"><span class="driver-status driver-status-{{ $driver->status }}">{{ $driver->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="driver-cell-actions"><div class="table-row-actions"><a href="{{ route('admin.employees.show', $driver) }}" aria-label="Lihat pegawai {{ $driver->full_name }}" title="Lihat detail"><x-lucide-eye aria-hidden="true" /></a><a href="{{ route('admin.employees.edit', $driver) }}" aria-label="Edit pegawai {{ $driver->full_name }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.employees.destroy', $driver) }}" data-delete-confirm data-no-loading data-delete-name="Pegawai {{ $driver->full_name }}" data-delete-description="Pegawai yang sudah memiliki penilaian akan dinonaktifkan agar riwayat penilaian tetap tersimpan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus pegawai {{ $driver->full_name }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-admin.empty-state title="Belum ada pegawai" description="Tambahkan pegawai untuk mulai mengelola data karyawan." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="driver-pagination">
                <span>Menampilkan {{ $drivers->firstItem() ?? 0 }} - {{ $drivers->lastItem() ?? 0 }} dari {{ $drivers->total() }} pegawai</span>
                @if($drivers->hasPages())<x-admin.pagination :paginator="$drivers" label="Pagination pegawai" />@endif
            </footer>
        </section>
    </section>
</x-layouts.admin>
