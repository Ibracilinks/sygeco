@props([
    'title',
    'subtitle' => null,
    'icon' => '📖',
    'current' => 'index',
])

@php
    // Chapitres du manuel : le sommaire est partagé par toutes les pages.
    $chapitres = [
        'index' => ['route' => 'documentation', 'label' => 'Accueil du manuel', 'icone' => '🏠'],
        'directions-centrales' => ['route' => 'documentation.directions-centrales', 'label' => 'Directions Centrales', 'icone' => '🏢'],
        'utilisateurs' => ['route' => 'documentation.utilisateurs', 'label' => 'Utilisateurs', 'icone' => '👥'],
        'exercices' => ['route' => 'documentation.exercices', 'label' => 'Exercices', 'icone' => '📅'],
        'planification' => ['route' => 'documentation.planification', 'label' => 'Planification', 'icone' => '🎯'],
        'activites' => ['route' => 'documentation.activites', 'label' => 'Activités', 'icone' => '📋'],
        'validation' => ['route' => 'documentation.validation', 'label' => 'Validation', 'icone' => '✅'],
        'suivi' => ['route' => 'documentation.suivi', 'label' => 'Suivi & évaluation', 'icone' => '📊'],
        'missions' => ['route' => 'documentation.missions', 'label' => 'Missions', 'icone' => '🧳'],
    ];
@endphp

<x-layouts::app :title="$title">
    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6">
        {{-- En-tête --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-2xl dark:bg-sky-950/40">{{ $icon }}</div>
                <div>
                    @if ($current !== 'index')
                        <nav class="mb-1 flex flex-wrap items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                            <a href="{{ route('documentation') }}" class="hover:underline">Manuel d'utilisation</a>
                            <span class="text-slate-300 dark:text-slate-600">›</span>
                            <span class="text-slate-700 dark:text-slate-300">{{ $title }}</span>
                        </nav>
                    @endif
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[240px_1fr]">
            {{-- Sommaire général + sommaire de page --}}
            <aside class="lg:sticky lg:top-6 lg:self-start">
                <nav class="rounded-xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Chapitres</p>
                    <ul class="space-y-1">
                        @foreach ($chapitres as $cle => $chapitre)
                            <li>
                                <a href="{{ route($chapitre['route']) }}"
                                   @class([
                                       'flex items-center gap-2 rounded px-2 py-1.5 transition',
                                       'bg-sky-50 font-medium text-sky-800 dark:bg-sky-950/40 dark:text-sky-200' => $current === $cle,
                                       'text-slate-600 hover:bg-slate-100 hover:text-sky-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-sky-300' => $current !== $cle,
                                   ])>
                                    <span>{{ $chapitre['icone'] }}</span>
                                    <span>{{ $chapitre['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @isset($sommaire)
                        <p class="mb-2 mt-4 border-t border-slate-200 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:border-slate-700">Dans cette page</p>
                        <ul class="space-y-1">
                            {{ $sommaire }}
                        </ul>
                    @endisset
                </nav>
            </aside>

            {{-- Contenu --}}
            <div class="space-y-6">
                {{ $slot }}

                <p class="pb-6 text-center text-xs text-slate-400">SYGECO — CANAM · Besoin d'aide supplémentaire ? Contactez la DBCGOQ.</p>
            </div>
        </div>
    </div>
</x-layouts::app>
