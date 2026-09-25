<x-layouts.admin title="Master Kendaraan">
    <x-slot:pageActions>
        <form class="master-filter-panel" method="GET" action="{{ route('admin.vehicles.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label><span>UNIT</span><select name="branch_id" onchange="this.form.requestSubmit()" aria-label="Filter Unit Kerja"><option value="">Semua Unit Kerja</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) request('branch_id') === $branch->id)>{{ $branch->name }}</option>@endforeach</select></label>
            <label><span>STATUS</span><select name="status" onchange="this.form.requestSubmit()" aria-label="Filter status"><option value="">Semua Status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select></label>
            <label><span>KELENGKAPAN</span><select name="completeness" onchange="this.form.requestSubmit()" aria-label="Filter kelengkapan data"><option value="">Semua Data</option><option value="complete" @selected(request('completeness') === 'complete')>Lengkap</option><option value="incomplete" @selected(request('completeness') === 'incomplete')>Belum Lengkap</option></select></label>
            @if (request()->filled('search') || request()->filled('branch_id') || request()->filled('status') || request()->filled('completeness'))<a class="secondary-button assessment-reset-button" href="{{ route('admin.vehicles.index') }}" title="Reset filter" aria-label="Reset filter"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>@endif
            <a class="primary-button master-create-button" href="{{ route('admin.vehicles.create') }}" title="Tambah kendaraan" aria-label="Tambah kendaraan"><x-lucide-plus aria-hidden="true" /><span>Tambah</span></a>
        </form>
    </x-slot:pageActions>
    <section class="vehicle-list-card master-table-card">

        <div class="table-wrap vehicle-table-wrap">
            <table class="data-table vehicle-data-table">
                <colgroup>
                    <col class="vehicle-col-photo">
                    <col class="vehicle-col-police">
                    <col class="vehicle-col-brand">
                    <col class="vehicle-col-year">
                    <col class="vehicle-col-color">
                    <col class="vehicle-col-branch">
                    <col class="vehicle-col-status">
                    <col class="vehicle-col-qr">
                    <col class="vehicle-col-actions">
                </colgroup>
                <thead>
                    <tr>
                        <th class="vehicle-column-photo">Foto</th>
                        <th>No. Polisi</th>
                        <th>Merk / Tipe</th>
                        <th>Tahun</th>
                        <th>Warna</th>
                        <th class="vehicle-column-branch">Unit Kerja (Cabang)</th>
                        <th class="vehicle-column-status">Status</th>
                        <th class="vehicle-column-qr">QR Code</th>
                        <th class="vehicle-column-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        @php($qrDataUri = app(App\Services\VehicleQrCodeService::class)->dataUri($vehicle))
                        <tr>
                            <td>
                                <div class="vehicle-photo-thumbnail">
                                    @if ($vehicle->photo)
                                        <img src="{{ Str::startsWith($vehicle->photo, ['http://', 'https://', '/']) ? $vehicle->photo : asset('storage/' . $vehicle->photo) }}" alt="{{ $vehicle->police_number }}">
                                    @else
                                        <x-lucide-car-front aria-hidden="true" />
                                    @endif
                                </div>
                            </td>
                            <td class="vehicle-cell-police">{{ $vehicle->police_number }}</td>
                            <td>{{ trim($vehicle->brand . ' ' . $vehicle->model) ?: '-' }}</td>
                            <td>{{ $vehicle->year ?: '-' }}</td>
                            <td>{{ $vehicle->color ?: '-' }}</td>
                            <td class="vehicle-cell-branch">{{ $vehicle->branch?->name ?: '-' }}</td>
                            <td class="vehicle-cell-status"><span class="vehicle-status vehicle-status-{{ $vehicle->status }}">{{ $vehicle->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <button
                                    class="vehicle-qr-trigger"
                                    type="button"
                                    data-vehicle-qr-trigger
                                    data-qr-src="{{ $qrDataUri }}"
                                    data-qr-title="{{ $vehicle->police_number }}"
                                    data-qr-description="{{ trim($vehicle->brand . ' ' . $vehicle->model) ?: 'Kendaraan' }} - {{ $vehicle->branch?->name ?: 'Cabang belum tersedia' }}"
                                    data-qr-download="{{ route('admin.vehicles.qr.download', $vehicle) }}"
                                    aria-label="Tampilkan QR Code {{ $vehicle->police_number }}"
                                    title="Tampilkan QR Code"
                                >
                                    <img class="vehicle-qr-thumbnail" src="{{ $qrDataUri }}" alt="QR {{ $vehicle->police_number }}">
                                </button>
                            </td>
                            <td>
                                <div class="table-row-actions">
                                    <a href="{{ route('admin.vehicles.show', $vehicle) }}" aria-label="Lihat kendaraan {{ $vehicle->police_number }}" title="Lihat"><x-lucide-eye aria-hidden="true" /></a>
                                    <a href="{{ route('admin.vehicles.edit', $vehicle) }}" aria-label="Edit kendaraan {{ $vehicle->police_number }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a>
                                    <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}" data-delete-confirm data-no-loading data-delete-name="Kendaraan {{ $vehicle->police_number }}" data-delete-description="Kendaraan yang sudah memiliki penilaian akan dinonaktifkan agar riwayat penilaian tetap tersimpan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus kendaraan {{ $vehicle->police_number }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><x-admin.empty-state title="Belum ada kendaraan" description="Tambahkan kendaraan untuk menyiapkan QR Code penilaian." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="vehicle-pagination">
            <span>Menampilkan {{ $vehicles->firstItem() ?? 0 }} - {{ $vehicles->lastItem() ?? 0 }} dari {{ $vehicles->total() }} data</span>
            @if ($vehicles->hasPages())
                <x-admin.pagination :paginator="$vehicles" label="Pagination kendaraan" />
            @endif
        </footer>
    </section>

    @include('admin.vehicles._qr-modal')
</x-layouts.admin>
