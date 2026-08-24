@php
    $score = is_numeric($value) ? min(5, max(0, (float) $value)) : 0;
    $circumference = 276.46;
    $offset = $circumference * (1 - ($score / 5));
@endphp

<div class="rating-gauge">
    <div class="rating-gauge-chart">
        <svg viewBox="0 0 104 104" role="img" aria-label="{{ $label }}: {{ $value ?? '-' }} dari 5">
            <circle class="rating-gauge-track" cx="52" cy="52" r="44" />
            <circle class="rating-gauge-progress" cx="52" cy="52" r="44" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" />
        </svg>
        <div class="rating-gauge-value"><strong>{{ $value ?? '-' }}</strong><span>/ 5</span></div>
    </div>
    <div class="rating-gauge-copy"><strong>{{ $label }}</strong><span>Skala penilaian 1 sampai 5</span></div>
</div>
