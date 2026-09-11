@props(['title' => 'Dashboard'])
@php($subtitles = ['Dashboard' => 'Ringkasan aktivitas penilaian driver dan kendaraan.', 'Master Cabang' => 'Kelola data cabang atau unit kerja.', 'Tambah Unit Kerja' => 'Buat data unit kerja baru.', 'Edit Unit Kerja' => 'Perbarui data unit kerja.', 'Detail Unit Kerja' => 'Informasi unit kerja dan data terkait.', 'Master Pegawai' => 'Kelola data pegawai secara lengkap, akurat, dan terstruktur.', 'Tambah Pegawai' => 'Buat data pegawai baru.', 'Edit Pegawai' => 'Perbarui data pegawai.', 'Detail Pegawai' => 'Informasi pegawai dan data terkait.', 'Kategori Pegawai' => 'Kelola kategori dan kebutuhan data SIM pegawai.', 'Tambah Kategori Pegawai' => 'Buat kategori pegawai baru.', 'Edit Kategori Pegawai' => 'Perbarui kategori pegawai.', 'Master Kendaraan' => 'Kelola data kendaraan dan QR Code.', 'Tambah Kendaraan' => 'Buat data kendaraan baru.', 'Edit Kendaraan' => 'Perbarui data kendaraan.', 'Detail Kendaraan' => 'Informasi kendaraan dan data terkait.', 'Kategori Indikator' => 'Kelola kategori indikator untuk master pertanyaan.', 'Tambah Kategori Indikator' => 'Buat kategori indikator baru.', 'Edit Kategori Indikator' => 'Perbarui kategori indikator.', 'Master Pertanyaan' => 'Kelola pertanyaan penilaian.', 'Tambah Pertanyaan' => 'Buat konfigurasi pertanyaan baru.', 'Edit Pertanyaan' => 'Perbarui konfigurasi pertanyaan.', 'Detail Pertanyaan' => 'Informasi konfigurasi pertanyaan.', 'Pengguna' => 'Kelola akun administrator dan akses aplikasi.', 'Edit Admin' => 'Perbarui data dan akses akun administrator.', 'Detail Admin' => 'Informasi akun administrator.', 'Monitoring' => 'Pantau aktivitas penilaian yang masuk.', 'Report Driver' => 'Analisis kinerja penilaian driver.', 'Report Kendaraan' => 'Analisis kinerja penilaian kendaraan.', 'Profil Sistem' => 'Kelola identitas dan informasi bantuan sistem.', 'Log Aktivitas' => 'Riwayat aktivitas administrator untuk audit aplikasi.', 'Permintaan Kerjasama' => 'Kelola permintaan penawaran yang dikirim melalui landing page.', 'Detail Permintaan Kerjasama' => 'Informasi lengkap calon mitra dan kebutuhan layanan.'])
<?php
    $isDashboard = $title === 'Dashboard';
    $isDriverIndex = $title === 'Master Pegawai';
?>
<?php
    $masterSearch = match ($title) {
        'Master Cabang' => ['route' => 'admin.branches.index', 'placeholder' => 'Cari kode, unit kerja, PIC, atau kontak...', 'filters' => ['status']],
        'Master Kendaraan' => ['route' => 'admin.vehicles.index', 'placeholder' => 'Cari nomor polisi, merk, atau Unit Kerja...', 'filters' => ['branch_id', 'status']],
        'Master Pertanyaan' => ['route' => 'admin.questions.index', 'placeholder' => 'Cari pertanyaan atau indikator...', 'filters' => ['target_type', 'status']],
        'Kategori Indikator' => ['route' => 'admin.indicator-categories.index', 'placeholder' => 'Cari kategori indikator...', 'filters' => ['target_type', 'status']],
        'Permintaan Kerjasama' => ['route' => 'admin.partnership-inquiries.index', 'placeholder' => 'Cari perusahaan, PIC, kontak, atau layanan...', 'filters' => ['start_date', 'end_date', 'status']],
        default => null,
    };
    $pageSearch = match ($title) {
        'Riwayat Penilaian' => ['route' => 'admin.assessments.index', 'placeholder' => 'Cari driver, kendaraan, atau unit kerja...', 'filters' => ['start_date', 'end_date', 'branch_id', 'driver_id', 'vehicle_id']],
        'Rekap Penilaian' => ['route' => 'admin.assessments.recap', 'placeholder' => 'Cari driver, kendaraan, atau unit kerja...', 'filters' => ['start_date', 'end_date', 'branch_id', 'driver_id', 'vehicle_id', 'group']],
        'Report Driver' => ['route' => 'admin.reports.drivers', 'placeholder' => 'Cari driver, kendaraan, atau unit kerja...', 'filters' => ['start_date', 'end_date', 'branch_id', 'driver_id']],
        'Report Kendaraan' => ['route' => 'admin.reports.vehicles', 'placeholder' => 'Cari driver, kendaraan, atau unit kerja...', 'filters' => ['start_date', 'end_date', 'branch_id', 'vehicle_id']],
        'Report Unit Kerja' => ['route' => 'admin.reports.branches', 'placeholder' => 'Cari unit kerja...', 'filters' => ['start_date', 'end_date', 'branch_id']],
        'Log Aktivitas' => ['route' => 'admin.activity-logs.index', 'placeholder' => 'Cari aktivitas, modul, atau admin...', 'filters' => ['start_date', 'end_date', 'user_id']],
        default => null,
    };
    $searchConfig = $pageSearch ?? $masterSearch;
    $hasGlobalSearch = $isDashboard || $isDriverIndex || $searchConfig !== null;
    $currentUser = auth()->user();
    $notificationsReadAt = $currentUser?->notifications_read_at;
    $recentNotifications = \App\Models\ActivityLog::query()
        ->with('user:id,name')
        ->latest('created_at')
        ->take(2)
        ->get();
    $unreadNotifications = \App\Models\ActivityLog::query()
        ->when($notificationsReadAt, fn ($query) => $query->where('created_at', '>', $notificationsReadAt))
        ->count();
?>

<header class="admin-header">
    <button class="icon-button mobile-menu-button" type="button" data-sidebar-toggle aria-label="Buka menu admin">
        <x-lucide-menu aria-hidden="true" />
    </button>
    @if ($hasGlobalSearch)
    <form class="admin-global-search" action="{{ $isDashboard ? route('admin.dashboard') : ($searchConfig ? route($searchConfig['route']) : route('admin.employees.index')) }}" method="GET" role="search" @if($isDashboard) data-dashboard-search-form @endif @if($isDriverIndex) data-driver-search-form @endif @if($searchConfig) data-master-search-form @endif>
        @if ($isDashboard)
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
            <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
        @endif
        <x-lucide-search aria-hidden="true" />
        @if ($isDriverIndex)
            <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        @if ($searchConfig)
            @foreach ($searchConfig['filters'] as $filter)
                <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
            @endforeach
        @endif
        <input name="search" type="search" placeholder="{{ $isDashboard ? 'Cari driver, kendaraan, atau Unit Kerja...' : ($isDriverIndex ? 'Cari nama pegawai, nomor SIM, atau No. HP...' : ($searchConfig['placeholder'] ?? 'Cari data...')) }}" aria-label="Cari data" value="{{ request('search') }}" @if($isDashboard) data-dashboard-search @endif @if($isDriverIndex) data-driver-search @endif @if($searchConfig) data-master-search @endif>
    </form>
    @endif

    <div class="admin-header-title">
        <h1>{{ $title }}</h1>
        <p>{{ $subtitles[$title] ?? 'Kelola sistem penilaian driver dan kendaraan.' }}</p>
    </div>

    <details class="admin-notification-menu" data-notification-menu data-notification-read-url="{{ route('admin.notifications.read') }}">
        <summary class="admin-notification-button" aria-label="Buka notifikasi" title="Notifikasi">
            <x-lucide-bell aria-hidden="true" />
            @if ($unreadNotifications > 0)<span data-notification-indicator aria-hidden="true"></span>@endif
        </summary>
        <div class="admin-notification-dropdown" role="menu">
            <header><strong>Notifikasi</strong><span data-notification-count>{{ $unreadNotifications > 0 ? $unreadNotifications.' Baru' : 'Sudah dibaca' }}</span></header>
            <div class="admin-notification-list">
                @forelse ($recentNotifications as $notification)
                    @if (auth()->user()?->role === \App\Models\User::ROLE_ADMIN)
                        <a class="admin-notification-item" href="{{ route('admin.activity-logs.index') }}" role="menuitem">
                            <i aria-hidden="true"></i>
                            <div><strong>{{ $notification->description ?: $notification->action }}</strong><small>{{ $notification->module }}{{ $notification->created_at ? ' · '.$notification->created_at->diffForHumans() : '' }}</small></div>
                        </a>
                    @else
                        <article class="admin-notification-item">
                            <i aria-hidden="true"></i>
                            <div><strong>{{ $notification->description ?: $notification->action }}</strong><small>{{ $notification->module }}{{ $notification->created_at ? ' · '.$notification->created_at->diffForHumans() : '' }}</small></div>
                        </article>
                    @endif
                @empty
                    <p class="admin-notification-empty">Belum ada notifikasi baru.</p>
                @endforelse
            </div>
            @if (auth()->user()?->role === \App\Models\User::ROLE_ADMIN)
                <a href="{{ route('admin.activity-logs.index') }}">Lihat Semua</a>
            @endif
        </div>
    </details>

    <details class="admin-profile-menu" data-profile-menu>
        <summary class="admin-profile-trigger" data-profile-trigger aria-expanded="false" aria-haspopup="menu" aria-label="Buka menu akun" title="Menu akun">
            <span class="admin-profile-copy"><strong>{{ auth()->user()->name ?? 'Admin' }}</strong><small>Super Admin</small></span>
            <span class="admin-profile-avatar">
                @if (auth()->user()?->photo)
                    <img src="{{ str_starts_with(auth()->user()->photo, 'http') ? auth()->user()->photo : asset('storage/'.auth()->user()->photo) }}" alt="Foto profil {{ auth()->user()->name }}">
                @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                @endif
            </span>
        </summary>

        <div class="admin-profile-dropdown" data-profile-dropdown role="menu">
            <a class="admin-profile-dropdown-user" href="{{ route('admin.users.show', auth()->user()) }}" role="menuitem">
                <span class="admin-profile-dropdown-avatar">
                    @if (auth()->user()?->photo)
                        <img src="{{ str_starts_with(auth()->user()->photo, 'http') ? auth()->user()->photo : asset('storage/'.auth()->user()->photo) }}" alt="Foto profil {{ auth()->user()->name }}">
                    @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    @endif
                </span>
                <span class="admin-profile-dropdown-copy"><strong>{{ auth()->user()->name ?? 'Admin' }}</strong><span>{{ auth()->user()->email ?? '' }}</span></span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" role="menuitem"><x-lucide-log-out aria-hidden="true" /><span>Logout</span></button>
            </form>
        </div>
    </details>
</header>

