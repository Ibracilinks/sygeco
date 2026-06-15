@props([
    'variant' => 'neutral',
    'icon' => null,
    'label' => null,
    'href' => null,
    'type' => 'button',
    'iconOnly' => false,
])

@php
    // Une couleur unique par action, en style « discret » : texte coloré + fond léger au survol,
    // décliné pour le mode clair et le mode sombre.
    $variants = [
        'view'       => 'text-sky-700 hover:bg-sky-100 dark:text-sky-300 dark:hover:bg-sky-500/15',
        'edit'       => 'text-amber-700 hover:bg-amber-100 dark:text-amber-300 dark:hover:bg-amber-500/15',
        'delete'     => 'text-rose-700 hover:bg-rose-100 dark:text-rose-300 dark:hover:bg-rose-500/15',
        'activate'   => 'text-emerald-700 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-500/15',
        'deactivate' => 'text-zinc-600 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-600/40',
        'create'     => 'text-indigo-700 hover:bg-indigo-100 dark:text-indigo-300 dark:hover:bg-indigo-500/15',
        'duplicate'  => 'text-violet-700 hover:bg-violet-100 dark:text-violet-300 dark:hover:bg-violet-500/15',
        'submit'     => 'text-blue-700 hover:bg-blue-100 dark:text-blue-300 dark:hover:bg-blue-500/15',
        'validate'   => 'text-teal-700 hover:bg-teal-100 dark:text-teal-300 dark:hover:bg-teal-500/15',
        'refuse'     => 'text-red-700 hover:bg-red-100 dark:text-red-300 dark:hover:bg-red-500/15',
        'neutral'    => 'text-zinc-600 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-600/40',
    ];

    $base = 'inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-current/40 disabled:opacity-50 disabled:pointer-events-none';

    $classes = trim($base . ' ' . ($variants[$variant] ?? $variants['neutral']));

    // Libellé accessible même quand seul l'icône est visible.
    $accessible = $label ?? \Illuminate\Support\Str::headline($variant);
@endphp

@php
    $iconSvg = match ($icon) {
        'eye' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
        'pencil' => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>',
        'trash' => '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>',
        'power' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"/>',
        'pause' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5"/>',
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>',
        'duplicate' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/>',
        'send' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        'x-mark' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        default => null,
    };
@endphp

@if ($href)
    <a href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($iconOnly) title="{{ $accessible }}" aria-label="{{ $accessible }}" @endif>
        @if ($iconSvg)
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">{!! $iconSvg !!}</svg>
        @endif
        @unless ($iconOnly)
            <span>{{ $label ?? $slot }}</span>
        @endunless
    </a>
@else
    <button type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($iconOnly) title="{{ $accessible }}" aria-label="{{ $accessible }}" @endif>
        @if ($iconSvg)
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">{!! $iconSvg !!}</svg>
        @endif
        @unless ($iconOnly)
            <span>{{ $label ?? $slot }}</span>
        @endunless
    </button>
@endif
