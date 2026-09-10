<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/bds/bds-logo.png') }}">
    <title>Penilaian {{ $vehicle->police_number }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
    @endif
</head>
<body class="passenger-shell">
    <main class="passenger-entry">
        <section class="passenger-card">
            <p class="eyebrow">QR Kendaraan Valid</p>
            <h1>{{ $vehicle->police_number }}</h1>
            <p>{{ $vehicle->brand }} {{ $vehicle->model }} · {{ $vehicle->branch?->name }}</p>
            <x-admin.status-badge tone="success">Kendaraan Aktif</x-admin.status-badge>
            <div class="passenger-note">Alur penilaian penumpang akan dilanjutkan pada phase berikutnya.</div>
        </section>
    </main>
</body>
</html>
