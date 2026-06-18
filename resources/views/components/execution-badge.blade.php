@props(['statut' => 'non_realise'])

@php
    $map = [
        'realise' => ['Réalisé', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200'],
        'en_cours' => ['En cours', 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'],
        'non_realise' => ['Non réalisé', 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-100'],
    ];
    [$label, $cls] = $map[$statut] ?? $map['non_realise'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . $cls]) }}>{{ $label }}</span>
