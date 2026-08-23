<x-layouts.admin title="Detail Cabang">
    <div class="resource-page-navigation">
        <a class="primary-button" href="{{ route('admin.branches.index') }}">
            <x-lucide-arrow-left aria-hidden="true" />
            <span>Kembali</span>
        </a>
    </div>
    <div class="detail-layout branch-detail-layout">
        <x-admin.panel title="Informasi Cabang">
            <div class="detail-grid">
                <x-admin.detail-row label="Kode" :value="$branch->code" />
                <x-admin.detail-row label="Cabang Unit Kerja" :value="$branch->name" />
                <x-admin.detail-row label="Status" :value="$branch->status === 'active' ? 'Aktif' : 'Nonaktif'" />
                <x-admin.detail-row label="Alamat" :value="$branch->address" />
                <x-admin.detail-row label="Kabupaten / Kota" :value="$branch->regency" />
                <x-admin.detail-row label="PIC Unit" :value="$branch->pic_name" />
                <x-admin.detail-row label="No. Telepon" :value="$branch->phone" />
                <x-admin.detail-row label="Email" :value="$branch->email" />
            </div>
        </x-admin.panel>
        <x-admin.panel title="Ringkasan">
            <div class="summary-grid"><div><strong>{{ $branch->drivers_count }}</strong><span>Driver</span></div><div><strong>{{ $branch->vehicles_count }}</strong><span>Kendaraan</span></div><div><strong>{{ $branch->ratings_count }}</strong><span>Rating</span></div></div>
            <div class="form-actions stack-actions">
                <a class="primary-button" href="{{ route('admin.branches.edit', ['branch' => $branch, 'return_to' => 'detail']) }}">Edit</a>
                <form method="POST" action="{{ route('admin.branches.toggle-status', $branch) }}">@csrf @method('PATCH')<button class="secondary-button" type="submit">{{ $branch->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" data-delete-confirm data-no-loading data-delete-name="Unit kerja {{ $branch->name }}" data-delete-description="Jika unit kerja ini memiliki data terkait, unit kerja beserta driver dan kendaraannya akan dinonaktifkan.">@csrf @method('DELETE')<button class="danger-button" type="submit">Hapus</button></form>
            </div>
        </x-admin.panel>
    </div>
</x-layouts.admin>
