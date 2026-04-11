@props([
    'title' => null,
    'subtitle' => null,
    'height' => 'h-auto',
    'class' => '',
])

<div
    class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 {{ $class }}">
    @if ($title)
        <div class="border-b border-neutral-200 dark:border-neutral-700 px-6 py-4">
            <div>
                <h3 class="text-lg font-semibold dark:text-white">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif
    <div class="p-6 {{ $height }}">
        {{ $slot }}
    </div>
</div>  
