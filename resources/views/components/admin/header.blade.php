@props(['title' => 'Dashboard'])
@php($subtitles = ['Dashboard' => 'Ringkasan aktivitas penilaian driver dan kendaraan.', 'Master Cabang' => 'Kelola data cabang atau unit kerja.', 'Tambah Cabang' => 'Buat data unit kerja baru.', 'Edit Cabang' => 'Perbarui data unit kerja.', 'Detail Cabang' => 'Informasi unit kerja dan data terkait.', 'Master Driver' => 'Kelola data driver secara lengkap, akurat, dan terstruktur.', 'Tambah Driver' => 'Buat data driver baru.', 'Edit Driver' => 'Perbarui data driver.', 'Detail Driver' => 'Informasi driver dan data terkait.', 'Master Kendaraan' => 'Kelola data kendaraan dan QR Code.', 'Tambah Kendaraan' => 'Buat data kendaraan baru.', 'Edit Kendaraan' => 'Perbarui data kendaraan.', 'Detail Kendaraan' => 'Informasi kendaraan dan QR Code.', 'Master Pertanyaan' => 'Kelola pertanyaan penilaian.', 'Tambah Pertanyaan' => 'Buat konfigurasi pertanyaan baru.', 'Edit Pertanyaan' => 'Perbarui konfigurasi pertanyaan.', 'Detail Pertanyaan' => 'Informasi konfigurasi pertanyaan.', 'Pengguna' => 'Kelola akun administrator dan akses aplikasi.', 'Edit Admin' => 'Perbarui data dan akses akun administrator.', 'Detail Admin' => 'Informasi akun administrator.', 'Monitoring' => 'Pantau aktivitas penilaian yang masuk.', 'Report Driver' => 'Analisis kinerja penilaian driver.', 'Report Kendaraan' => 'Analisis kinerja penilaian kendaraan.', 'Profil Sistem' => 'Kelola identitas dan informasi bantuan sistem.', 'Log Aktivitas' => 'Riwayat aktivitas administrator untuk audit aplikasi.'])

<header class="admin-header">
    <button class="icon-button mobile-menu-button" type="button" data-sidebar-toggle aria-label="Buka menu admin">
        <x-lucide-menu aria-hidden="true" />
    </button>

    <div class="admin-header-title">
        <h1>{{ $title }}</h1>
        <p>{{ $subtitles[$title] ?? 'Kelola sistem penilaian driver dan kendaraan.' }}</p>
    </div>

    <div class="admin-profile-menu" data-profile-menu>
        <button class="admin-profile-trigger" type="button" data-profile-trigger aria-expanded="false" aria-haspopup="menu">
            <span class="admin-profile-copy"><strong>{{ auth()->user()->name ?? 'Admin' }}</strong><small>Super Admin</small></span>
            <span class="admin-profile-avatar">
                @if (auth()->user()?->photo)
                    <img src="{{ str_starts_with(auth()->user()->photo, 'http') ? auth()->user()->photo : asset('storage/'.auth()->user()->photo) }}" alt="Foto profil {{ auth()->user()->name }}">
                @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                @endif
            </span>
        </button>

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
    </div>
</header>
