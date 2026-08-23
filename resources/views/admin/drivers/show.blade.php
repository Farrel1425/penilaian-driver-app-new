<x-layouts.admin title="Detail Driver">
    @php
        $photoUrl = $driver->photo ? (Str::startsWith($driver->photo, ['http://', 'https://', '/']) ? $driver->photo : asset('storage/'.$driver->photo)) : null;
        $simPhotoUrl = $driver->sim_photo ? (Str::startsWith($driver->sim_photo, ['http://', 'https://', '/']) ? $driver->sim_photo : asset('storage/'.$driver->sim_photo)) : null;
        $gender = $driver->gender === 'male' ? 'Laki-laki' : ($driver->gender === 'female' ? 'Perempuan' : null);
    @endphp

    <div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.drivers.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>
    <x-admin.panel title="Detail Driver">
        <div class="driver-detail-view">
            <div class="driver-detail-profile">
                <div class="driver-detail-profile-photo">
                    @if ($photoUrl)<img src="{{ $photoUrl }}" alt="Foto {{ $driver->full_name }}">@else<x-lucide-user aria-hidden="true" />@endif
                </div>
                <strong>{{ $driver->full_name }}</strong>
                <span>{{ $driver->nickname ?: 'Driver operasional' }}</span>
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
                <div><dt>No. SIM</dt><dd>{{ $driver->sim_number }}</dd></div>
                <div><dt>Jenis SIM</dt><dd>{{ App\Models\Driver::SIM_TYPES[$driver->sim_type] ?? null }}</dd></div>
                <div><dt>Berlaku Hingga</dt><dd>{{ $driver->sim_expired_at?->format('d/m/Y') }}</dd></div>
                <div><dt>Unit Kerja</dt><dd>{{ $driver->branch?->name }}</dd></div>
                <div><dt>Tanggal Bergabung</dt><dd>{{ $driver->join_date?->format('d/m/Y') }}</dd></div>
            </dl>

            <div class="driver-detail-sim-photo">
                <span>Foto SIM</span>
                @if ($simPhotoUrl)
                    <img src="{{ $simPhotoUrl }}" alt="Foto SIM {{ $driver->full_name }}">
                @else
                    <div><x-lucide-image aria-hidden="true" /><small>Belum ada foto SIM</small></div>
                @endif
            </div>
        </div>
    </x-admin.panel>
    <x-admin.panel title="Aksi">
        <div class="summary-grid"><div><strong>{{ $driver->ratings_count }}</strong><span>Total Rating</span></div></div>
        <div class="form-actions stack-actions"><a class="primary-button" href="{{ route('admin.drivers.edit', ['driver' => $driver, 'return_to' => 'detail']) }}">Edit</a><form method="POST" action="{{ route('admin.drivers.toggle-status', $driver) }}">@csrf @method('PATCH')<button class="secondary-button" type="submit">{{ $driver->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button></form><form method="POST" action="{{ route('admin.drivers.destroy', $driver) }}" data-delete-confirm data-no-loading data-delete-name="Driver {{ $driver->full_name }}" data-delete-description="Driver yang sudah memiliki penilaian akan dinonaktifkan agar riwayat penilaian tetap tersimpan.">@csrf @method('DELETE')<button class="danger-button" type="submit">Hapus</button></form></div>
    </x-admin.panel>
</x-layouts.admin>
