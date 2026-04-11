@props([
    'id' => 'treemap-' . uniqid(),
    'data' => [], // [['name' => '...', 'value' => ...], ...]
    'height' => 400,
])

@php
    $chartId = 'treemap-' . uniqid();
    $total = !empty($data) ? array_sum(array_column($data, 'value')) : 0;
@endphp

<div>
    @if (empty($data))
        <div class="flex items-center justify-center h-64 text-zinc-500 dark:text-zinc-400">
            Aucune donnée à afficher
        </div>
    @else
        <div id="{{ $chartId }}" class="grid gap-1"
            style="grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); height: {{ $height }}px;">
            @foreach ($data as $item)
                @php
                    $percentage = $total > 0 ? ($item['value'] / $total) * 100 : 0;
                    $area = max(10, min(40, sqrt($percentage) * 8));
                @endphp
                <div class="relative overflow-hidden rounded-lg bg-linear-to-br from-blue-500 to-blue-600 p-4 text-white"
                    style="grid-row: span {{ max(1, ceil($area / 10)) }}; grid-column: span {{ max(1, ceil($area / 10)) }};">
                    <div class="absolute bottom-2 left-2">
                        <div class="text-xs font-medium">{{ $item['name'] }}</div>
                        <div class="text-lg font-bold">{{ number_format($item['value'] / 1000000, 1, ',', ' ') }} M</div>
                        <div class="text-xs opacity-75">{{ number_format($percentage, 1) }}%</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
