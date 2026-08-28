@php
    $points = collect($trend)->values();
    $width = max(620, $points->count() * 74);
    $height = 244;
    $padding = ['left' => 38, 'right' => 22, 'top' => 20, 'bottom' => 43];
    $plotWidth = $width - $padding['left'] - $padding['right'];
    $plotHeight = $height - $padding['top'] - $padding['bottom'];
    $count = max($points->count() - 1, 1);
    $coordinates = $points->map(function ($point, $index) use ($padding, $plotWidth, $plotHeight, $count) {
        $value = (float) ($point['average'] ?? 0);
        return [
            'x' => $padding['left'] + ($plotWidth * $index / $count),
            'y' => $padding['top'] + (($plotHeight * (5 - min(max($value, 0), 5))) / 5),
            'label' => \Carbon\Carbon::parse($point['date'])->translatedFormat('d M'),
            'value' => number_format($value, 2),
            'count' => $point['count'],
        ];
    });
    $polyline = $coordinates->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
@endphp

@if($points->isNotEmpty())
    <div class="recap-chart-scroll" tabindex="0" aria-label="Grafik ringkasan nilai harian">
        <svg class="recap-summary-chart" viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="Grafik rata-rata penilaian harian">
            @foreach([0, 1, 2, 3, 4, 5] as $grid)
                @php($y = $padding['top'] + (($plotHeight * (5 - $grid)) / 5))
                <line x1="{{ $padding['left'] }}" x2="{{ $width - $padding['right'] }}" y1="{{ $y }}" y2="{{ $y }}" class="recap-chart-grid" />
                <text x="{{ $padding['left'] - 12 }}" y="{{ $y + 4 }}" text-anchor="end" class="recap-chart-axis">{{ $grid }}</text>
            @endforeach
            <polyline points="{{ $polyline }}" class="recap-chart-line" />
            @foreach($coordinates as $point)
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4.5" class="recap-chart-point"><title>{{ $point['label'] }}: {{ $point['value'] }} ({{ $point['count'] }} penilaian)</title></circle>
                <text x="{{ $point['x'] }}" y="{{ $height - 16 }}" text-anchor="middle" class="recap-chart-axis">{{ $point['label'] }}</text>
            @endforeach
        </svg>
    </div>
@else
    <x-admin.empty-state title="Belum ada grafik" description="Grafik akan muncul setelah penilaian masuk." />
@endif
