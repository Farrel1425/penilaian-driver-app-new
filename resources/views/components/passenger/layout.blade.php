@props(['title' => 'Penilaian', 'variant' => 'default'])
@php
    $systemName = App\Models\SystemSetting::systemName();
    $systemLogoUrl = App\Models\SystemSetting::logoUrl();
    $supportContact = App\Models\SystemSetting::supportContact();
    $supportContactUrl = App\Models\SystemSetting::supportContactUrl();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ $systemLogoUrl }}">
    <title>{{ $title }} | {{ $systemName }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
    @endif
</head>
<body class="passenger-shell passenger-app">
    <header class="passenger-app-brand">
        <img src="{{ $systemLogoUrl }}" alt="Logo {{ $systemName }}">
        <div><strong>{{ $systemName }}</strong><span>Supported by Bank BPD Bali</span></div>
        <div class="passenger-app-brand-actions">
            @if ($supportContactUrl)
                <a href="{{ $supportContactUrl }}" aria-label="Hubungi bantuan melalui {{ $supportContact }}" title="Hubungi bantuan: {{ $supportContact }}" @if (str_starts_with($supportContactUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif><x-lucide-circle-help aria-hidden="true" /></a>
            @endif
            <a href="{{ route('home') }}" aria-label="Informasi aplikasi" title="Informasi aplikasi"><x-lucide-info aria-hidden="true" /></a>
        </div>
    </header>
    <main @class(['passenger-flow', 'passenger-flow-' . $variant => $variant !== 'default'])>
        {{ $slot }}
    </main>
</body>
</html>
