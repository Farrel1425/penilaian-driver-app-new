@props(['src' => null, 'type', 'alt' => ''])

@php
    $fallback = $type === 'vehicle' ? 'images/defaults/vehicle.png' : 'images/defaults/driver.png';
    $usesFallback = blank($src);
    $url = filled($src)
        ? (Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '/']) ? $src : asset('storage/'.$src))
        : asset($fallback);
@endphp

<img src="{{ $url }}" alt="{{ $alt }}" {{ $attributes->class([
    'entity-photo-default' => $usesFallback,
    'entity-photo-default-'.$type => $usesFallback,
]) }}>
