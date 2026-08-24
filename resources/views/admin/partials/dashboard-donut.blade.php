@php
    $total = array_sum($distribution);
    $colors = [5 => '#2463eb', 4 => '#27a967', 3 => '#f5b942', 2 => '#fa885d', 1 => '#ec6283'];
    $labels = [5 => 'Sangat Baik', 4 => 'Baik', 3 => 'Cukup', 2 => 'Buruk', 1 => 'Sangat Buruk'];
    $circumference = 251.33;
    $offset = 0;
@endphp
<div class="dashboard-donut">
    <div class="dashboard-donut-chart"><svg viewBox="0 0 104 104" role="img" aria-label="Distribusi rating"><circle class="dashboard-donut-track" cx="52" cy="52" r="40" />@foreach([5, 4, 3, 2, 1] as $score)@php($portion = $total ? ($distribution[$score] / $total) * $circumference : 0)<circle cx="52" cy="52" r="40" class="dashboard-donut-slice" stroke="{{ $colors[$score] }}" stroke-dasharray="{{ $portion }} {{ $circumference - $portion }}" stroke-dashoffset="{{ -$offset }}" />@php($offset += $portion)@endforeach</svg><div><strong>{{ $total }}</strong><span>{{ $label }}</span></div></div>
    <div class="dashboard-donut-legend">@foreach([5, 4, 3, 2, 1] as $score)<div><i style="background: {{ $colors[$score] }}"></i><span>{{ $score }} ({{ $labels[$score] }})</span><b>{{ $total ? round(($distribution[$score] / $total) * 100) : 0 }}%</b></div>@endforeach</div>
</div>
