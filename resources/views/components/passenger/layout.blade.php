@props(['title' => 'Penilaian', 'variant' => 'default'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/bds/bds-logo.png') }}">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
    @endif
</head>
@php($systemName = App\Models\SystemSetting::value('system_name', 'Aplikasi Penilaian Driver'))
@php($systemLogoUrl = App\Models\SystemSetting::logoUrl())
<body class="passenger-shell passenger-app">
    <header class="passenger-app-brand">
        <img src="{{ $systemLogoUrl }}" alt="Logo {{ $systemName }}">
        <div><strong>{{ $systemName }}</strong><span>Supported by Bank BPD Bali</span></div>
        <div class="passenger-app-brand-actions" aria-hidden="true"><x-lucide-circle-help /><x-lucide-info /></div>
    </header>
    <main @class(['passenger-flow', 'passenger-flow-' . $variant => $variant !== 'default'])>
        {{ $slot }}
    </main>
</body>
</html>
