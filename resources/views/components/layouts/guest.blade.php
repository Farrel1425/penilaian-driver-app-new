@php
    $systemName = App\Models\SystemSetting::systemName();
    $systemLogoUrl = App\Models\SystemSetting::logoUrl();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ $systemLogoUrl }}">
    <title>{{ isset($title) ? $title.' | '.$systemName : $systemName }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
        <script defer>{!! file_get_contents(resource_path('js/app.js')) !!}</script>
    @endif
</head>
<body class="guest-shell auth-app">
    <x-notifications.toast />
    <div class="app-loading-overlay" data-app-loading hidden aria-live="polite" aria-busy="true">
        <div class="app-loading-indicator">
            <div class="app-loading-animation" data-app-loading-animation="{{ asset('images/app-loading-car.json') }}" aria-hidden="true"></div>
            <span class="sr-only">Memuat halaman...</span>
        </div>
    </div>
    {{ $slot }}
</body>
</html>
