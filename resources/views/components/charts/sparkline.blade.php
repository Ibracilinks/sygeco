@props([
    'id' => 'sparkline-' . uniqid(),
    'data' => [],
    'color' => '#3b82f6',
    'height' => 40,
])

@php
    $chartId = 'sparkline-' . uniqid();
    $minValue = min($data);
    $maxValue = max($data);
    $points = [];
    $width = 100;
    $step = $width / (count($data) - 1);

    foreach ($data as $i => $value) {
        $x = $i * $step;
        $y = $height - (($value - $minValue) / ($maxValue - $minValue ?: 1)) * $height;
        $points[] = "$x,$y";
    }
    $path = 'M ' . implode(' L ', $points);
@endphp

<div>
    <svg width="100%" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}"
        preserveAspectRatio="none">
        <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="{{ $color }}" stroke-width="1.5" />
    </svg>
</div>
