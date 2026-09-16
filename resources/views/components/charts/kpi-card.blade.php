@props([
    'title' => null,
    'value' => 0,
    'previous' => null,
    'unit' => null,
    'trend' => null, // 'up', 'down', 'neutral'
    'color' => 'blue',
    'icon' => null,
    'divisor' => 1, // Divide value by this number
    'decimals' => 0, // Number of decimal places
])

@php
    $colors = [
        'blue' => 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300',
        'green' => 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300',
        'red' => 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300',
        'purple' => 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300',
    ];

    $accents = [
        'blue' => 'bg-blue-500',
        'green' => 'bg-green-500',
        'red' => 'bg-red-500',
        'yellow' => 'bg-yellow-500',
        'purple' => 'bg-purple-500',
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

    $displayValue = is_numeric($value) ? $value / $divisor : $value;
    $percentageChange = $previous ? (($value - $previous) / $previous) * 100 : null;

    // Tracé SVG (heroicons outline) par nom d'icône, rendu en <svg> stroke comme le composant <x-action>.
    $iconSvg = match ($icon) {
        'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
        'folder' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>',
        'clipboard-document-list' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>',
        'currency-dollar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        'banknotes' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
        'calculator' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z"/>',
        'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
        'trending-up', 'arrow-trending-up' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>',
        default => null,
    };
@endphp

<div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/20">
    <div class="absolute inset-x-0 top-0 h-1 {{ $accents[$color] ?? $accents['blue'] }}"></div>

    <div class="flex items-start justify-between">
        <div class="min-w-0">
            @if ($title)
                <p class="truncate text-sm font-medium text-slate-500 dark:text-slate-400">{{ $title }}</p>
            @endif
            <div class="flex items-baseline gap-2 mt-2">
                <span
                    class="text-3xl font-bold text-slate-900 dark:text-white">{{ is_numeric($displayValue) ? number_format($displayValue, $decimals, ',', ' ') : $displayValue }}</span>
                @if ($unit)
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $unit }}</span>
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
                    <span class="text-sm {{ $trendColors[$trend] ?? 'text-slate-500' }}">
                        {{ abs($percentageChange) }}% vs période précédente
                    </span>
                </div>
            @endif
        </div>

        @if ($iconSvg)
            <div class="shrink-0 rounded-xl {{ $colors[$color] ?? $colors['blue'] }} p-3 ring-1 ring-inset ring-black/5 transition-transform duration-200 group-hover:scale-105 dark:ring-white/5">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">{!! $iconSvg !!}</svg>
            </div>
        @endif
    </div>
</div>
