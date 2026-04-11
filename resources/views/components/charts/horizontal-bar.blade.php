@props([
    'id' => 'horizontal-bar-' . uniqid(),
    'data' => [], // [['label' => '...', 'value' => ...], ...]
    'height' => 400,
])

@php
    $chartId = 'horizontal-bar-' . uniqid();
    $labels = array_column($data, 'label');
    $values = array_column($data, 'value');
    $maxValue = !empty($values) ? max($values) : 1;
@endphp

<div>
    @if (empty($data))
        <div class="flex items-center justify-center h-64 text-zinc-500 dark:text-zinc-400">
            Aucune donnée à afficher
        </div>
    @else
        <div id="{{ $chartId }}" style="height: {{ $height }}px;">
            @foreach ($data as $item)
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="dark:text-white truncate" style="max-width: 60%;">{{ $item['label'] }}</span>
                        <span class="font-semibold dark:text-white">{{ number_format($item['value'], 1, ',', ' ') }} M
                            FCFA</span>
                    </div>
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                        @php
                            $percentage = $maxValue > 0 ? ($item['value'] / $maxValue) * 100 : 0;
                        @endphp
                        <div class="bg-linear-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500"
                            style="width: {{ min($percentage, 100) }}%">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
