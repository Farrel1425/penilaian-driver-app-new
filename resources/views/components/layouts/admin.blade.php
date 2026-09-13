<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/bds/bds-logo.png') }}">
    <title>{{ trim(($title ?? '') . ' | ' . config('app.name', 'Penilaian Driver'), ' |') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
        <script defer>{!! file_get_contents(resource_path('js/app.js')) !!}</script>
    @endif
</head>
<body class="admin-shell admin-app" data-admin-session-timeout="1800000" data-admin-session-ping="{{ route('admin.session.ping') }}" data-admin-logout="{{ route('logout') }}" data-admin-login="{{ route('login') }}">
    <x-notifications.toast />
    <div class="app-loading-overlay" data-app-loading hidden aria-live="polite" aria-busy="true">
        <div class="app-loading-indicator">
            <div class="app-loading-animation" data-app-loading-animation="{{ asset('images/app-loading-car.json') }}" aria-hidden="true"></div>
            <span class="sr-only">Memuat halaman...</span>
        </div>
    </div>
    <x-admin.delete-confirmation-modal />
    <div class="admin-frame">
        <x-admin.sidebar />

        <div class="admin-main">
            <x-admin.header :title="$title ?? 'Dashboard'" />

            <main class="admin-content">
                @php
                    $sectionByTitle = [
                        'Dashboard' => 'Dashboard',
                        'Master Cabang' => 'Master Data',
                        'Master Pegawai' => 'Master Data',
                        'Kategori Pegawai' => 'Master Data',
                        'Kategori Indikator' => 'Master Data',
                        'Master Kendaraan' => 'Master Data',
                        'Master Pertanyaan' => 'Master Data',
                        'Tambah Unit Kerja' => 'Master Data',
                        'Edit Unit Kerja' => 'Master Data',
                        'Detail Unit Kerja' => 'Master Data',
                        'Tambah Pegawai' => 'Master Data',
                        'Edit Pegawai' => 'Master Data',
                        'Detail Pegawai' => 'Master Data',
                        'Tambah Kategori Pegawai' => 'Master Data',
                        'Edit Kategori Pegawai' => 'Master Data',
                        'Tambah Kategori Indikator' => 'Master Data',
                        'Edit Kategori Indikator' => 'Master Data',
                        'Tambah Kendaraan' => 'Master Data',
                        'Edit Kendaraan' => 'Master Data',
                        'Detail Kendaraan' => 'Master Data',
                        'Tambah Pertanyaan' => 'Master Data',
                        'Edit Pertanyaan' => 'Master Data',
                        'Detail Pertanyaan' => 'Master Data',
                        'Riwayat Penilaian' => 'Penilaian',
                        'Rekap Penilaian' => 'Penilaian',
                        'Monitoring' => 'Penilaian',
                        'Detail Monitoring' => 'Penilaian',
                        'Preview Penilaian' => 'Penilaian',
                        'Laporan Monitoring' => 'Penilaian',
                        'Report Driver' => 'Laporan',
                        'Report Kendaraan' => 'Laporan',
                        'Report Unit Kerja' => 'Laporan',
                        'Log Aktivitas' => 'Pengaturan',
                        'Pengguna' => 'Pengaturan',
                        'Profil Sistem' => 'Pengaturan',
                        'Permintaan Kerjasama' => 'Landing Page',
                        'Detail Permintaan Kerjasama' => 'Landing Page',
                    ];
                    $pageTitle = $title ?? 'Dashboard';
                    $pageSection = $sectionByTitle[$pageTitle] ?? 'Sistem';
                    $isResourcePage = Illuminate\Support\Str::startsWith($pageTitle, ['Tambah ', 'Edit ', 'Detail ']);
                @endphp
                <header @class([
                    'admin-page-heading',
                    'admin-page-heading--'.\Illuminate\Support\Str::slug($pageTitle),
                    'is-resource-page' => $isResourcePage,
                ])>
                    <div class="admin-page-heading-copy">
                        <p>{{ strtoupper($pageSection) }}@if (($title ?? '') !== $pageSection) <span>&rsaquo;</span> {{ strtoupper($title ?? '') }}@endif</p>
                        <h1>{{ $title ?? 'Dashboard' }}</h1>
                    </div>
                    @isset ($pageActions)
                        <div class="admin-page-heading-actions">{{ $pageActions }}</div>
                    @endisset
                </header>
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
