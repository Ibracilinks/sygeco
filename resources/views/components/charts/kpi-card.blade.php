@props([
    'title' => null,
    'value' => 0,
    'previous' => null,
    'unit' => null,
    'trend' => null, // 'up', 'down', 'neutral'
    'color' => 'blue',
    'icon' => null,
])

@php
    $colors = [
        'blue' => 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300',
        'green' => 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300',
        'red' => 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300',
        'purple' => 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300',
    ];

    $trendColors = [
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        'neutral' => 'text-yellow-600',
    ];

    $trendIcons = [
        'up' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        'down' => 'M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6',
        'neutral' => 'M5 12h14',
    ];

    $percentageChange = $previous ? (($value - $previous) / $previous) * 100 : null;
@endphp

<div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6">
    <div class="flex items-start justify-between">
        <div>
            @if ($title)
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $title }}</p>
            @endif
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-bold dark:text-white">{{ number_format($value, 0, ',', ' ') }}</span>
                @if ($unit)
                    <span class="text-sm text-zinc-500">{{ $unit }}</span>
                @endif
            </div>

            @if ($previous && $percentageChange !== null)
                <div class="flex items-center gap-1 mt-2">
                    @if ($trend)
                        <svg class="w-4 h-4 {{ $trendColors[$trend] }}" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $trendIcons[$trend] }}"></path>
                        </svg>
                    @endif
                    <span class="text-sm {{ $trendColors[$trend] ?? 'text-zinc-500' }}">
                        {{ abs($percentageChange) }}% vs période précédente
                    </span>
                </div>
            @endif
        </div>

        @if ($icon)
            <div class="rounded-lg {{ $colors[$color] ?? $colors['blue'] }} p-3">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
