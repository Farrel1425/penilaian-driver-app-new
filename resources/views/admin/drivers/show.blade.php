<x-layouts.admin title="Detail Pegawai">
    @php
        $simPhotoUrl = $driver->sim_photo ? (Str::startsWith($driver->sim_photo, ['http://', 'https://', '/']) ? $driver->sim_photo : asset('storage/'.$driver->sim_photo)) : null;
        $gender = $driver->gender === 'male' ? 'Laki-laki' : ($driver->gender === 'female' ? 'Perempuan' : null);
    @endphp

    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.employees.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot>
    <div class="detail-layout branch-detail-layout">
    <x-admin.panel title="Detail Pegawai">
        <div class="driver-detail-view">
            <div class="driver-detail-profile">
                <div class="driver-detail-profile-photo">
                    <x-entity-photo type="driver" :src="$driver->photo" alt="Foto {{ $driver->full_name }}" />
                </div>
                <strong>{{ $driver->full_name }}</strong>
                <span>{{ $driver->nickname ?: 'Pegawai' }}</span>
                <span class="driver-status driver-status-{{ $driver->status }}">{{ $driver->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
            </div>

            <dl class="driver-detail-list">
                <div><dt>Nama Lengkap</dt><dd>{{ $driver->full_name }}</dd></div>
                <div><dt>Nama Panggilan</dt><dd>{{ $driver->nickname }}</dd></div>
                <div><dt>Tempat, Tanggal Lahir</dt><dd>{{ $driver->birth_place }}, {{ $driver->birth_date?->format('d/m/Y') }}</dd></div>
                <div><dt>Jenis Kelamin</dt><dd>{{ $gender }}</dd></div>
                <div><dt>Alamat</dt><dd>{{ $driver->address }}</dd></div>
                <div><dt>No. HP / WhatsApp</dt><dd>{{ $driver->phone }}</dd></div>
                <div><dt>Email</dt><dd>{{ $driver->email }}</dd></div>
                <div><dt>Status Pernikahan</dt><dd>{{ App\Models\Driver::MARITAL_STATUSES[$driver->marital_status] ?? null }}</dd></div>
            </dl>

            <dl class="driver-detail-list">
                <div><dt>Kategori Pegawai</dt><dd>{{ $driver->employeeCategory?->name ?: '-' }}</dd></div>
                @if ($driver->employeeCategory?->requires_sim)
                    <div><dt>No. SIM</dt><dd>{{ $driver->sim_number ?: '-' }}</dd></div>
                    <div><dt>Jenis SIM</dt><dd>{{ App\Models\Driver::SIM_TYPES[$driver->sim_type] ?? '-' }}</dd></div>
                    <div><dt>Berlaku Hingga</dt><dd>{{ $driver->sim_expired_at?->format('d/m/Y') ?: '-' }}</dd></div>
                @endif
                <div><dt>Unit Kerja</dt><dd>{{ $driver->branch?->name }}</dd></div>
                <div><dt>Tanggal Bergabung</dt><dd>{{ $driver->join_date?->format('d/m/Y') }}</dd></div>
                <div><dt>Tanggal Berakhir</dt><dd>{{ $driver->end_date?->format('d/m/Y') ?: '-' }}</dd></div>
            </dl>

            @if ($driver->employeeCategory?->requires_sim)<div class="driver-detail-sim-photo">
                <span>Foto SIM</span>
                @if ($simPhotoUrl)
                    <img src="{{ $simPhotoUrl }}" alt="Foto SIM {{ $driver->full_name }}">
                @else
                    <div><x-lucide-image aria-hidden="true" /><small>Belum ada foto SIM</small></div>
                @endif
            </div>@endif
        </div>
    </x-admin.panel>
    <x-admin.panel title="Aksi Pegawai">
        <div class="summary-grid driver-total-rating"><div><strong>{{ $driver->ratings_count }}</strong><span>Total Rating</span></div></div>
        <div class="record-actions"><a class="primary-button" href="{{ route('admin.employees.edit', ['driver' => $driver, 'return_to' => 'detail']) }}"><x-lucide-pencil aria-hidden="true" /><span>Edit Pegawai</span></a><form method="POST" action="{{ route('admin.employees.toggle-status', $driver) }}" data-confirm data-no-loading data-confirm-title="{{ $driver->status === 'active' ? 'Nonaktifkan pegawai?' : 'Aktifkan pegawai?' }}" data-confirm-description="Status pegawai dapat diubah kembali kapan saja." data-confirm-label="{{ $driver->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}" data-confirm-tone="primary" data-confirm-icon="power">@csrf @method('PATCH')<button @class(['status-toggle-action', 'is-active' => $driver->status === 'active']) type="submit"><x-lucide-power aria-hidden="true" /><span>{{ $driver->status === 'active' ? 'Nonaktifkan Pegawai' : 'Aktifkan Pegawai' }}</span></button></form><form method="POST" action="{{ route('admin.employees.destroy', $driver) }}" data-delete-confirm data-no-loading data-delete-name="Pegawai {{ $driver->full_name }}" data-delete-description="Pegawai yang sudah memiliki penilaian akan dinonaktifkan agar riwayat penilaian tetap tersimpan.">@csrf @method('DELETE')<button class="danger-button" type="submit"><x-lucide-trash-2 aria-hidden="true" /><span>Hapus Pegawai</span></button></form></div>
    </x-admin.panel>
    </div>
</x-layouts.admin>
