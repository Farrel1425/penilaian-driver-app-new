@php($isSuperAdmin = auth()->user()?->role === \App\Models\User::ROLE_ADMIN)
@php($systemName = App\Models\SystemSetting::value('system_name', 'Aplikasi Penilaian Driver'))
@php($systemLogoUrl = App\Models\SystemSetting::logoUrl())

<aside id="admin-sidebar" class="admin-sidebar" data-admin-sidebar>
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}" aria-label="{{ $systemName }}">
        <img class="sidebar-brand-logo" src="{{ $systemLogoUrl }}" alt="Logo {{ $systemName }}">
        <span class="sidebar-brand-copy">
            <strong>{{ $systemName }}</strong>
            <span class="sidebar-brand-support"><i>Supported by</i><b>Bank BPD Bali</b></span>
        </span>
    </a>

    <nav class="sidebar-nav" aria-label="Navigasi admin">
        <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}"><x-lucide-layout-dashboard class="nav-icon" aria-hidden="true" /><span>Dashboard</span></a>

        @if ($isSuperAdmin)
            @php($masterActive = request()->routeIs('admin.branches.*', 'admin.employees.*', 'admin.employee-categories.*', 'admin.vehicles.*', 'admin.indicator-categories.*', 'admin.questions.*'))
            <details class="sidebar-nav-group" data-sidebar-group="master-data" @if($masterActive) open @endif>
                <summary class="sidebar-link sidebar-nav-group-toggle {{ $masterActive ? 'is-active' : '' }}" data-sidebar-group-toggle><x-lucide-database class="nav-icon" aria-hidden="true" /><span>Master Data</span><x-lucide-chevron-down class="sidebar-nav-chevron" aria-hidden="true" /></summary>
                <div class="sidebar-nav-submenu" aria-label="Master Data">
                    <a class="{{ request()->routeIs('admin.branches.*') ? 'is-active' : '' }}" href="{{ route('admin.branches.index') }}">Unit Kerja</a>
                    <a class="{{ request()->routeIs('admin.employee-categories.*') ? 'is-active' : '' }}" href="{{ route('admin.employee-categories.index') }}">Kategori Pegawai</a>
                    <a class="{{ request()->routeIs('admin.employees.*') ? 'is-active' : '' }}" href="{{ route('admin.employees.index') }}">Pegawai</a>
                    <a class="{{ request()->routeIs('admin.vehicles.*') ? 'is-active' : '' }}" href="{{ route('admin.vehicles.index') }}">Kendaraan</a>
                    <a class="{{ request()->routeIs('admin.indicator-categories.*') ? 'is-active' : '' }}" href="{{ route('admin.indicator-categories.index') }}">Kategori Indikator</a>
                    <a class="{{ request()->routeIs('admin.questions.*') ? 'is-active' : '' }}" href="{{ route('admin.questions.index') }}">Pertanyaan</a>
                </div>
            </details>
        @endif

        @php($assessmentActive = request()->routeIs('admin.assessments.index', 'admin.assessments.show', 'admin.assessments.recap'))
        <details class="sidebar-nav-group" data-sidebar-group="penilaian" @if($assessmentActive) open @endif>
            <summary class="sidebar-link sidebar-nav-group-toggle {{ $assessmentActive ? 'is-active' : '' }}" data-sidebar-group-toggle><x-lucide-clipboard-list class="nav-icon" aria-hidden="true" /><span>Penilaian</span><x-lucide-chevron-down class="sidebar-nav-chevron" aria-hidden="true" /></summary>
            <div class="sidebar-nav-submenu" aria-label="Penilaian">
                <a class="{{ request()->routeIs('admin.assessments.index', 'admin.assessments.show') ? 'is-active' : '' }}" href="{{ route('admin.assessments.index') }}">Riwayat Penilaian</a>
                <a class="{{ request()->routeIs('admin.assessments.recap') ? 'is-active' : '' }}" href="{{ route('admin.assessments.recap') }}">Rekap Penilaian</a>
            </div>
        </details>

        @php($reportActive = request()->routeIs('admin.reports.*'))
        <details class="sidebar-nav-group" data-sidebar-group="laporan" @if($reportActive) open @endif>
            <summary class="sidebar-link sidebar-nav-group-toggle {{ $reportActive ? 'is-active' : '' }}" data-sidebar-group-toggle><x-lucide-file-bar-chart class="nav-icon" aria-hidden="true" /><span>Laporan</span><x-lucide-chevron-down class="sidebar-nav-chevron" aria-hidden="true" /></summary>
            <div class="sidebar-nav-submenu" aria-label="Laporan">
                <a class="{{ request()->routeIs('admin.reports.drivers') ? 'is-active' : '' }}" href="{{ route('admin.reports.drivers') }}">Report Driver</a>
                <a class="{{ request()->routeIs('admin.reports.vehicles') ? 'is-active' : '' }}" href="{{ route('admin.reports.vehicles') }}">Report Kendaraan</a>
                <a class="{{ request()->routeIs('admin.reports.branches') ? 'is-active' : '' }}" href="{{ route('admin.reports.branches') }}">Report Unit Kerja</a>
            </div>
        </details>

        @if ($isSuperAdmin)
            <a class="sidebar-link {{ request()->routeIs('admin.partnership-inquiries.*') ? 'is-active' : '' }}" href="{{ route('admin.partnership-inquiries.index') }}"><x-lucide-inbox class="nav-icon" aria-hidden="true" /><span>Permintaan Kerjasama</span></a>

            @php($settingsActive = request()->routeIs('admin.settings.*', 'admin.activity-logs.*', 'admin.users.*'))
            <details class="sidebar-nav-group" data-sidebar-group="pengaturan" @if($settingsActive) open @endif>
                <summary class="sidebar-link sidebar-nav-group-toggle {{ $settingsActive ? 'is-active' : '' }}" data-sidebar-group-toggle><x-lucide-settings class="nav-icon" aria-hidden="true" /><span>Pengaturan</span><x-lucide-chevron-down class="sidebar-nav-chevron" aria-hidden="true" /></summary>
                <div class="sidebar-nav-submenu" aria-label="Pengaturan">
                    <a class="{{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}" href="{{ route('admin.settings.edit') }}">Profil Sistem</a>
                    <a class="{{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}" href="{{ route('admin.activity-logs.index') }}">Log Aktivitas</a>
                    <a class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}">Pengguna</a>
                </div>
            </details>
        @endif
    </nav>

    <button class="sidebar-collapse-button" type="button" data-sidebar-collapse aria-controls="admin-sidebar" aria-expanded="true" aria-label="Tutup navigasi samping" title="Tutup navigasi samping">
        <svg class="sidebar-collapse-contour" viewBox="0 0 20 124" preserveAspectRatio="none" aria-hidden="true">
            <path class="sidebar-collapse-contour-fill" d="M0 0 L18 14 V110 L0 124 Z" />
            <path class="sidebar-collapse-contour-line" d="M0 0 L18 14 V110 L0 124" />
        </svg>
        <x-lucide-chevron-right aria-hidden="true" />
    </button>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="sidebar-logout-button" type="submit"><x-lucide-log-out aria-hidden="true" /><span>Logout</span></button></form>
    </div>
</aside>




