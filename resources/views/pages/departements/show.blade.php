<x-layouts::app title="Détails Département">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $departement->nom }}</h1>
                <p class="font-mono text-sm text-slate-500 dark:text-slate-400">{{ $departement->code }}</p>
            </div>
            <div class="flex gap-2">
                @can('edit_departements')
                    <a href="{{ route('departements.edit', $departement) }}"
                        class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-500">
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
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($departement->users_count) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Activités</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($departement->activites_count) }}</p>
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
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Créé le</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ optional($departement->created_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Mis à jour le</div>
                        <div class="text-slate-800 dark:text-slate-100">{{ optional($departement->updated_at)->format('d/m/Y H:i') }}</div>
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

        <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Activités récentes</h2>
            </div>
            <div class="p-6">
                @if ($recentActivites->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucune activité associée pour le moment.</p>
                @else
                    <div class="overflow-x-auto">
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
                                        <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-100">{{ \Illuminate\Support\Str::limit($activite->nom_activite, 80) }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ ucfirst($activite->statut) }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ number_format((float) $activite->cout, 0, ',', ' ') }} FCFA</td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ optional($activite->created_at)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($departement->users as $user)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-100">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $user->email }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
