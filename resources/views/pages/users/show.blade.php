<x-layouts::app title="Détails utilisateur">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $user->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('edit_users')
                    <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-400">Modifier</a>
                @endcan
                <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Retour</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Département</p>
                <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $user->departement?->nom ?? 'Aucun' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Poste</p>
                <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $user->poste ?: 'Non défini' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Téléphone</p>
                <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $user->telephone ?: '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Nombre de rôles</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $user->roles_count }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900 xl:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Rôles attribués</h2>
                <div class="mt-4">
                    @if ($user->roles->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucun rôle attribué.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->roles as $role)
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-800 dark:bg-sky-900/40 dark:text-sky-200">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Métadonnées</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Créé le</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ optional($user->created_at)->format('d/m/Y H:i') ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Dernière mise à jour</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ optional($user->updated_at)->format('d/m/Y H:i') ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Statut email</dt>
                        <dd class="font-medium {{ $user->email_verified_at ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                            {{ $user->email_verified_at ? 'Vérifié' : 'Non vérifié' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        @if ($departementPeers->isNotEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Collègues du même département</h2>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($departementPeers as $peer)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/60">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $peer->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $peer->email }}</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $peer->poste ?: 'Poste non défini' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
