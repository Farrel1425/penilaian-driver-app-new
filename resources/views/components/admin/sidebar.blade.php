@php($systemSettings = \App\Models\SystemSetting::values())
@php($systemName = $systemSettings['system_name'] ?? 'Sistem Penilaian Driver')
@php($systemLogo = $systemSettings['logo'] ?? null)

<aside class="admin-sidebar" data-admin-sidebar>
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        <img class="sidebar-brand-logo" src="{{ $systemLogo ? (str_starts_with($systemLogo, ['http://', 'https://', '/']) ? $systemLogo : asset('storage/'.$systemLogo)) : asset('images/lais-logo-white.png') }}" alt="Logo {{ $systemName }}">
        <span class="sidebar-brand-name"><span>{{ $systemName }}</span></span>
    </a>

    <nav class="sidebar-nav" aria-label="Navigasi admin">
        <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}"><x-lucide-layout-dashboard class="nav-icon" aria-hidden="true" /><span>Dashboard</span></a>

        <p class="sidebar-label">Master Data</p>
        <a class="sidebar-link {{ request()->routeIs('admin.branches.*') ? 'is-active' : '' }}" href="{{ route('admin.branches.index') }}"><x-lucide-building-2 class="nav-icon" aria-hidden="true" /><span>Unit Kerja / Cabang</span></a>
        <a class="sidebar-link {{ request()->routeIs('admin.drivers.*') ? 'is-active' : '' }}" href="{{ route('admin.drivers.index') }}"><x-lucide-user-round class="nav-icon" aria-hidden="true" /><span>Driver</span></a>
        <a class="sidebar-link {{ request()->routeIs('admin.vehicles.*') ? 'is-active' : '' }}" href="{{ route('admin.vehicles.index') }}"><x-lucide-car-front class="nav-icon" aria-hidden="true" /><span>Kendaraan</span></a>
        <a class="sidebar-link {{ request()->routeIs('admin.questions.*') ? 'is-active' : '' }}" href="{{ route('admin.questions.index') }}"><x-lucide-clipboard-list class="nav-icon" aria-hidden="true" /><span>Pertanyaan</span></a>
        <p class="sidebar-label">Penilaian</p>
        <a class="sidebar-link {{ request()->routeIs('admin.assessments.index', 'admin.assessments.show') ? 'is-active' : '' }}" href="{{ route('admin.assessments.index') }}"><x-lucide-clipboard-list class="nav-icon" aria-hidden="true" /><span>Riwayat Penilaian</span></a>
        <a class="sidebar-link {{ request()->routeIs('admin.assessments.recap') ? 'is-active' : '' }}" href="{{ route('admin.assessments.recap') }}"><x-lucide-chart-column class="nav-icon" aria-hidden="true" /><span>Rekap Penilaian</span></a>

        <p class="sidebar-label">Laporan</p>
        <a class="sidebar-link {{ request()->routeIs('admin.reports.drivers') ? 'is-active' : '' }}" href="{{ route('admin.reports.drivers') }}"><x-lucide-file-bar-chart class="nav-icon" aria-hidden="true" /><span>Report Driver</span></a>
        <a class="sidebar-link {{ request()->routeIs('admin.reports.vehicles') ? 'is-active' : '' }}" href="{{ route('admin.reports.vehicles') }}"><x-lucide-file-chart-column class="nav-icon" aria-hidden="true" /><span>Report Kendaraan</span></a>
        <a class="sidebar-link {{ request()->routeIs('admin.reports.branches') ? 'is-active' : '' }}" href="{{ route('admin.reports.branches') }}"><x-lucide-building-2 class="nav-icon" aria-hidden="true" /><span>Report Unit Kerja</span></a>

        <p class="sidebar-label">Pengaturan</p>
        <a class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}" href="{{ route('admin.settings.edit') }}"><x-lucide-settings class="nav-icon" aria-hidden="true" /><span>Profil Sistem</span></a>
        <a class="sidebar-link {{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}" href="{{ route('admin.activity-logs.index') }}"><x-lucide-shield-check class="nav-icon" aria-hidden="true" /><span>Log Aktivitas</span></a>
        <a class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}"><x-lucide-users class="nav-icon" aria-hidden="true" /><span>Pengguna</span></a>
    </nav>
</aside>
