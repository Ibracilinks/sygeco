@props([
    'src',
    'alt',
])

<figure class="overflow-hidden rounded-xl border border-slate-200 shadow-sm dark:border-slate-700">
    <img src="{{ asset($src) }}" alt="{{ $alt }}" loading="lazy" class="w-full">
    @if ($slot->isNotEmpty())
        <figcaption class="border-t border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-950/40 dark:text-slate-400">
            {{ $slot }}
        </figcaption>
    @endif
</figure>
