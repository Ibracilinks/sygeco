@php
    /** @var string $type */
    $type = $type ?? \App\Models\Departement::TYPE_DEPARTEMENT;
    $libelle = \App\Models\Departement::TYPE_LABELS[$type] ?? ucfirst($type);
    $classes = match ($type) {
        \App\Models\Departement::TYPE_DIRECTION => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200',
        \App\Models\Departement::TYPE_SERVICE => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        \App\Models\Departement::TYPE_AGENCE_COMPTABLE => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
        \App\Models\Departement::TYPE_BUREAU_REGIONAL => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
        default => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
    };
@endphp
<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $classes }}">{{ $libelle }}</span>
