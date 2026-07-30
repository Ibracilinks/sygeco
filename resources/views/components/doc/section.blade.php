@props([
    'id',
    'titre',
    'chapo' => null,
])

<section id="{{ $id }}" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $titre }}</h2>
    @if ($chapo)
        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $chapo }}</p>
    @endif
    <div class="mt-4 space-y-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
        {{ $slot }}
    </div>
</section>
