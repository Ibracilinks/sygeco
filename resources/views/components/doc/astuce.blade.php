@props([
    'type' => 'info', // info | attention | interdit
])

@php
    $styles = [
        'info' => ['border-sky-200 bg-sky-50 dark:border-sky-900/70 dark:bg-sky-950/30', 'text-sky-900 dark:text-sky-100', '💡'],
        'attention' => ['border-amber-200 bg-amber-50 dark:border-amber-900/70 dark:bg-amber-950/30', 'text-amber-900 dark:text-amber-100', '⚠️'],
        'interdit' => ['border-rose-200 bg-rose-50 dark:border-rose-900/70 dark:bg-rose-950/30', 'text-rose-900 dark:text-rose-100', '⛔'],
    ];
    [$boite, $texte, $icone] = $styles[$type] ?? $styles['info'];
@endphp

<div class="flex gap-3 rounded-lg border p-4 {{ $boite }}">
    <span class="shrink-0">{{ $icone }}</span>
    <div class="text-xs leading-relaxed {{ $texte }}">{{ $slot }}</div>
</div>
