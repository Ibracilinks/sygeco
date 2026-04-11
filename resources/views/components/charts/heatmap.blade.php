@props([
    'id' => 'heatmap-' . uniqid(),
    'data' => [], // Matrice 2D
    'labels' => [],
    'height' => 300,
])

@php
    $chartId = 'heatmap-' . uniqid();
    $maxValue = max(array_merge(...$data));
@endphp

<div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="p-2"></th>
                    @foreach ($labels['x'] ?? [] as $label)
                        <th class="p-2 text-xs text-center dark:text-white">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $i => $row)
                    <tr>
                        <td class="p-2 text-xs font-medium dark:text-white">{{ $labels['y'][$i] ?? '' }}</td>
                        @foreach ($row as $j => $value)
                            @php
                                $intensity = $maxValue > 0 ? ($value / $maxValue) * 100 : 0;
                                $color = 'rgba(59, 130, 246, ' . $intensity / 100 . ')';
                            @endphp
                            <td class="p-2 text-center">
                                <div class="rounded-lg p-2 text-xs text-white"
                                    style="background-color: {{ $color }};">
                                    {{ number_format($value, 0, ',', ' ') }}
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
