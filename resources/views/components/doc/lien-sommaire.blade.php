@props([
    'ancre',
])

<li>
    <a href="#{{ $ancre }}" class="block rounded px-2 py-1 text-slate-600 transition hover:bg-slate-100 hover:text-sky-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-sky-300">{{ $slot }}</a>
</li>
