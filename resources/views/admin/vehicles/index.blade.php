<x-layouts.admin title="Master Kendaraan">
    <section class="vehicle-list-card">
        <form class="vehicle-list-toolbar" method="GET" action="{{ route('admin.vehicles.index') }}" data-debounced-search-form>
            <div class="vehicle-list-filters">
                <label class="vehicle-search-field">
                    <x-lucide-search aria-hidden="true" />
                    <input name="search" value="{{ request('search') }}" placeholder="Cari no. polisi, merk, atau unit kerja..." aria-label="Cari kendaraan" data-debounced-search>
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

            <a class="primary-button vehicle-create-button" href="{{ route('admin.vehicles.create') }}">
                <x-lucide-plus aria-hidden="true" />
                <span>Tambah Kendaraan</span>
            </a>
        </form>

        <div class="table-wrap vehicle-table-wrap">
            <table class="data-table vehicle-data-table">
                <thead>
                    <tr>
                        <th class="vehicle-column-number">No.</th>
                        <th class="vehicle-column-photo">Foto</th>
                        <th>No. Polisi</th>
                        <th>Merk / Tipe</th>
                        <th>Tahun</th>
                        <th>Warna</th>
                        <th>Unit Kerja (Cabang)</th>
                        <th>Status</th>
                        <th class="vehicle-column-qr">QR Code</th>
                        <th class="vehicle-column-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        @php($qrDataUri = app(App\Services\VehicleQrCodeService::class)->dataUri($vehicle))
                        <tr>
                            <td class="vehicle-cell-center">{{ $vehicles->firstItem() + $loop->index }}</td>
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
                            <td>{{ $vehicle->branch?->name ?: '-' }}</td>
                            <td><span class="vehicle-status vehicle-status-{{ $vehicle->status }}">{{ $vehicle->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
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
                        <tr><td colspan="10"><x-admin.empty-state title="Belum ada kendaraan" description="Tambahkan kendaraan untuk menyiapkan QR Code penilaian." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="vehicle-pagination">
            <span>Menampilkan {{ $vehicles->firstItem() ?? 0 }} - {{ $vehicles->lastItem() ?? 0 }} dari {{ $vehicles->total() }} data</span>
            @if ($vehicles->hasPages())
                <nav aria-label="Pagination kendaraan">
                    @if ($vehicles->onFirstPage())
                        <span class="vehicle-page-button is-disabled"><x-lucide-chevron-left aria-hidden="true" /></span>
                    @else
                        <a class="vehicle-page-button" href="{{ $vehicles->previousPageUrl() }}" aria-label="Halaman sebelumnya"><x-lucide-chevron-left aria-hidden="true" /></a>
                    @endif
                    <span class="vehicle-page-button is-current">{{ $vehicles->currentPage() }}</span>
                    @if ($vehicles->hasMorePages())
                        <a class="vehicle-page-button" href="{{ $vehicles->nextPageUrl() }}" aria-label="Halaman berikutnya"><x-lucide-chevron-right aria-hidden="true" /></a>
                    @else
                        <span class="vehicle-page-button is-disabled"><x-lucide-chevron-right aria-hidden="true" /></span>
                    @endif
                </nav>
            @endif
        </footer>
    </section>

    <div class="vehicle-qr-modal" data-vehicle-qr-modal hidden>
        <button class="vehicle-qr-modal-backdrop" type="button" data-vehicle-qr-close aria-label="Tutup popup QR"></button>
        <section class="vehicle-qr-dialog" role="dialog" aria-modal="true" aria-labelledby="vehicle-qr-modal-title">
            <button class="vehicle-qr-close" type="button" data-vehicle-qr-close aria-label="Tutup popup QR">
                <x-lucide-x aria-hidden="true" />
            </button>
            <span class="vehicle-qr-dialog-label">QR Kendaraan</span>
            <h2 id="vehicle-qr-modal-title" data-vehicle-qr-title>QR Kendaraan</h2>
            <p data-vehicle-qr-description></p>
            <div class="vehicle-qr-large-frame">
                <img data-vehicle-qr-image src="" alt="">
            </div>
            <a class="primary-button vehicle-qr-download" data-vehicle-qr-download href="#">
                <x-lucide-download aria-hidden="true" />
                <span>Download QR</span>
            </a>
        </section>
    </div>
</x-layouts.admin>
