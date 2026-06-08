<x-layouts::app title="Détails Département">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $departement->nom }}</h1>
                <p class="font-mono text-sm text-slate-500 dark:text-slate-400">{{ $departement->code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('departements.edit', $departement) }}"
                    class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-white transition hover:bg-amber-500">
                    Modifier
                </a>
                <a href="{{ route('departements.index') }}"
                    class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-white transition hover:bg-slate-600">
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Utilisateurs</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $departement->users_count }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $departement->activites_count }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</p>
                @if ($departement->is_active)
                    <p class="mt-2 inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-sm font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Actif</p>
                @else
                    <p class="mt-2 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-sm font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Inactif</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Informations générales</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Description</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->description ?? 'Aucune description' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Ordre</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->ordre ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Créé le</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Mis à jour le</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->updated_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Statut</div>
                        <div>
                            @if ($departement->is_active)
                                <span class="text-emerald-600">Actif</span>
                            @else
                                <span class="text-amber-600">Inactif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Responsable et équipe</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Responsable</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->responsable?->name ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Email</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->responsable?->email ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Téléphone</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->responsable?->telephone ?? 'Non défini' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Utilisateurs</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ $departement->users_count }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Activités récentes</h2>
            </div>
            <div class="p-6">
                @if ($recentActivites->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucune activité associée pour le moment.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($recentActivites as $activite)
                            <li class="flex items-center justify-between rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $activite->nom_activite }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $activite->created_at?->format('d/m/Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ number_format($activite->cout ?? 0, 0, ',', ' ') }} FCFA</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ ucfirst($activite->statut ?? '-') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        @if ($departement->users->count() > 0)
            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Utilisateurs associés
                        ({{ $departement->users->count() }})</h2>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <x-layouts::app title="Détails Département">
                                <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">

                                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $departement->nom }}</h1>
                                            <p class="font-mono text-sm text-slate-500 dark:text-slate-400">{{ $departement->code }}</p>
                                        </div>
                                        <div class="flex gap-2">
                                            @can('edit_departements')
                                                <a href="{{ route('departements.edit', $departement) }}"
                                                    class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700">
                                                    Modifier
                                                </a>
                                            @endcan
                                            <a href="{{ route('departements.index') }}"
                                                class="inline-flex items-center rounded-lg bg-slate-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600">
                                                Retour
                                            </a>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Utilisateurs</p>
                                            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($departement->users_count) }}</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités liées</p>
                                            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($departement->activites_count) }}</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</p>
                                            <p class="mt-2 text-sm font-semibold {{ $departement->is_active ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                                                {{ $departement->is_active ? 'Actif' : 'Inactif' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                                            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Informations générales</h2>
                                            </div>
                                            <div class="space-y-4 p-6">
                                                <div>
                                                    <div class="text-sm text-slate-500 dark:text-slate-400">Description</div>
                                                    <div class="text-slate-800 dark:text-slate-100">{{ $departement->description ?: 'Aucune description' }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-sm text-slate-500 dark:text-slate-400">Ordre d'affichage</div>
                                                    <div class="text-slate-800 dark:text-slate-100">{{ $departement->ordre ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                                            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Responsable</h2>
                                            </div>
                                            <div class="space-y-4 p-6 text-slate-800 dark:text-slate-100">
                                                <div>
                                                    <div class="text-sm text-slate-500 dark:text-slate-400">Nom</div>
                                                    <div>{{ $departement->responsable?->name ?? 'Non défini' }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-sm text-slate-500 dark:text-slate-400">Email</div>
                                                    <div>{{ $departement->responsable?->email ?? 'Non défini' }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-sm text-slate-500 dark:text-slate-400">Téléphone</div>
                                                    <div>{{ $departement->responsable?->telephone ?? 'Non défini' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($recentActivites->isNotEmpty())
                                        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                                            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Dernières activités</h2>
                                            </div>
                                            <div class="overflow-x-auto p-6">
                                                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                                    <thead class="bg-slate-50 dark:bg-slate-950">
                                                        <tr>
                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Activité</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Statut</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Coût</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Création</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                                        @foreach ($recentActivites as $activite)
                                                            <tr>
                                                                <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-100">{{ Str::limit($activite->nom_activite, 70) }}</td>
                                                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ ucfirst($activite->statut) }}</td>
                                                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ number_format((float) $activite->cout, 0, ',', ' ') }} FCFA</td>
                                                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ optional($activite->created_at)->format('d/m/Y') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($departement->users->isNotEmpty())
                                        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                                            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Utilisateurs associés ({{ $departement->users->count() }})</h2>
                                            </div>
                                            <div class="overflow-x-auto p-6">
                                                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                                    <thead class="bg-slate-50 dark:bg-slate-950">
                                                        <tr>
                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Nom</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Email</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Rôle</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                                        @foreach ($departement->users as $user)
                                                            <tr>
                                                                <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-100">{{ $user->name ?? ($user->nom ?? 'Utilisateur') }}</td>
                                                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $user->email }}</td>
                                                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $user->roles?->pluck('name')->join(', ') ?: '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </x-layouts::app>
