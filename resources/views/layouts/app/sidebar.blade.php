<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="app-shell min-h-screen bg-white dark:bg-zinc-950">
    @php
        $currentUser = auth()->user();
        // Exercice de travail courant (session), et non l'année de l'URL.
        $exerciceCourant = \App\Support\ActiveExercice::model();
    @endphp

    {{-- w-72 remplace le w-64 par défaut de Flux ; l'état replié (w-14) reste prioritaire. --}}
    <flux:sidebar sticky collapsible="mobile"
        class="app-sidebar w-72 border-e border-zinc-200 bg-zinc-50/95 dark:border-zinc-800 dark:bg-zinc-900/95">
        <flux:sidebar.header class="app-sidebar-header">
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            {{-- Réduction de la sidebar : en panneau sur mobile, en bande d'icônes sur desktop. --}}
            <flux:sidebar.collapse />
        </flux:sidebar.header>

        <div class="app-sidebar-intro hidden lg:block in-data-flux-sidebar-collapsed-desktop:lg:hidden">
            <p class="app-sidebar-intro-kicker">CANAM</p>
            <p class="app-sidebar-intro-title">Centre de pilotage</p>
            <div class="app-sidebar-intro-chip">
                <span>{{ __('Exercice') }}</span>
                <span>{{ $exerciceCourant?->annee ?? now()->year }}</span>
            </div>
        </div>

        <flux:sidebar.nav class="app-sidebar-nav">

            <!-- Dashboard -->
            <flux:sidebar.group expandable icon="squares-2x2" :heading="__('Navigation')"
                class="app-sidebar-group grid" data-groupe="navigation">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="presentation-chart-line" href="{{ route('sap.analytics') }}" target="_blank">
                    SAP Cloud Analytics
                </flux:sidebar.item>
            </flux:sidebar.group>

            <!-- Organisation -->
            @if ($currentUser->hasAnyRole(['superadmin', 'dbcgoq']))
                <flux:sidebar.group expandable icon="building-office-2" :heading="__('Organisation')"
                    class="app-sidebar-group grid" data-groupe="organisation">
                    <flux:sidebar.item icon="building-office" href="{{ route('departements.index') }}"
                        :current="request()->routeIs('departements.*')">
                        {{ __('Directions Centrales') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="users" href="{{ route('users.index') }}"
                        :current="request()->routeIs('users.*')">
                        {{ __('Utilisateurs') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endif

            @can('view_missions')
                <flux:sidebar.group expandable icon="document-duplicate" :heading="__('Missions')"
                    class="app-sidebar-group grid" data-groupe="missions">
                    {{-- Un accès direct par type de mission : chaque entrée filtre la liste. --}}
                    @php
                        $typeMissionCourant = request()->routeIs('missions.index') ? (string) request('type') : null;
                    @endphp

                    <flux:sidebar.item icon="building-office" href="{{ route('missions.index', ['type' => \App\Models\Mission::TYPE_MEME_VILLE]) }}"
                        :current="$typeMissionCourant === \App\Models\Mission::TYPE_MEME_VILLE">
                        {{ __('Missions même ville') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="globe-alt" href="{{ route('missions.index', ['type' => \App\Models\Mission::TYPE_EXTERIEURE]) }}"
                        :current="$typeMissionCourant === \App\Models\Mission::TYPE_EXTERIEURE">
                        {{ __('Missions à l\'étranger') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="map" href="{{ route('missions.index', ['type' => \App\Models\Mission::TYPE_REGION]) }}"
                        :current="$typeMissionCourant === \App\Models\Mission::TYPE_REGION">
                        {{ __('Missions intérieur du pays') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document-duplicate" href="{{ route('missions.index') }}"
                        :current="request()->routeIs('missions.*') && $typeMissionCourant === null">
                        {{ __('Toutes les missions') }}
                    </flux:sidebar.item>

                    @if ($currentUser->hasAnyRole(['superadmin', 'dbcgoq']))
                        <flux:sidebar.item icon="adjustments-horizontal" href="{{ route('mission-baremes.index') }}"
                            :current="request()->routeIs('mission-baremes.*')">
                            {{ __('Barèmes des missions') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            @endcan

            <!-- Planification Stratégique -->
            <flux:sidebar.group expandable icon="chart-bar-square" :heading="__('Planification Stratégique')"
                class="app-sidebar-group grid" data-groupe="planification">
                @if ($currentUser->hasAnyRole(['superadmin', 'dbcgoq']))
                    <flux:sidebar.item icon="calendar-days" href="{{ route('exercices.index') }}"
                        :current="request()->routeIs('exercices.*')">
                        {{ __('Exercices') }}
                    </flux:sidebar.item>
                @endif

                @if ($currentUser->hasAnyRole(['superadmin', 'dbcgoq', 'chef', 'agent-planification', 'suivi-evaluation']))
                    <flux:sidebar.item icon="chart-pie" href="{{ route('objectifs.index') }}"
                        :current="request()->routeIs('objectifs.*')">
                        {{ __('Objectifs') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document-text" href="{{ route('resultats.index') }}"
                        :current="request()->routeIs('resultats.*')">
                        {{ __('Résultats') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document-text" href="{{ route('extrants.index') }}"
                        :current="request()->routeIs('extrants.*')">
                        {{ __('Extrants') }}
                    </flux:sidebar.item>
                @endif

                <flux:sidebar.item icon="clipboard-document-list" href="{{ route('activites.index') }}"
                    :current="request()->routeIs('activites.index') || request()->routeIs('activites.create') || request()->routeIs('activites.edit') || request()->routeIs('activites.show')">
                    {{ __('Programmation / Planification') }}
                </flux:sidebar.item>

                @if ($currentUser->hasAnyRole(['superadmin', 'dbcgoq']))
                    <flux:sidebar.item icon="chart-pie" href="{{ route('budget.analysis') }}"
                        :current="request()->routeIs('budget.analysis')">
                        {{ __('Analyse Budgétaire') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="check-badge" href="{{ route('validations.index') }}"
                        :current="request()->routeIs('validations.*')">
                        {{ __('Arbitrage / Validation') }}
                        @php $nbEnAttente = App\Models\Activite::where('statut', 'en_attente')->count(); @endphp
                        @if ($nbEnAttente > 0)
                            <flux:badge class="ml-auto">{{ $nbEnAttente }}</flux:badge>
                        @endif
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="book-open-text" href="{{ route('journal.index') }}"
                        :current="request()->routeIs('journal.*')">
                        {{ __('Journal') }}
                    </flux:sidebar.item>
                @endif

                @php($nbNonLues = auth()->user()?->unreadNotifications()->count() ?? 0)
                <flux:sidebar.item icon="bell" href="{{ route('notifications.index') }}"
                    :badge="$nbNonLues > 0 ? $nbNonLues : null"
                    :current="request()->routeIs('notifications.*')">
                    {{ __('Notifications') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <!-- Suivi & Évaluation -->
            @unless ($currentUser->hasRole('agent-planification'))
                <flux:sidebar.group expandable icon="chart-bar" :heading="__('Suivi & Évaluation')"
                    class="app-sidebar-group grid" data-groupe="evaluation">
                    <flux:sidebar.item icon="chart-bar" href="{{ route('evaluations.index', 'mi-parcours') }}"
                        :current="request()->fullUrlIs(route('evaluations.index', 'mi-parcours').'*')">
                        {{ __('Mi-parcours') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="chart-bar-square" href="{{ route('evaluations.index', 'fin-annee') }}"
                        :current="request()->fullUrlIs(route('evaluations.index', 'fin-annee').'*')">
                        {{ __("Fin d'année") }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endunless
        </flux:sidebar.nav>

        <flux:spacer />

        <x-desktop-user-menu class="app-sidebar-user hidden border-t border-slate-200/80 pt-3 lg:block dark:border-slate-800" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="app-topbar border-b border-zinc-200/80 bg-white/95 lg:hidden dark:border-zinc-800 dark:bg-zinc-900/95">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <x-notifications-menu />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" class="w-full cursor-pointer"
                        data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    <script>
        // Mémorise l'état plié/déplié de chaque groupe de la sidebar d'une page à l'autre.
        // Les groupes sont des <ui-disclosure> Flux : l'attribut `open` porte leur état.
        (function () {
            const CLE = 'sygeco.sidebar.groupes';

            const lire = () => {
                try {
                    return JSON.parse(localStorage.getItem(CLE)) || {};
                } catch (e) {
                    return {};
                }
            };

            const appliquer = () => {
                const etats = lire();

                document.querySelectorAll('[data-groupe]').forEach((groupe) => {
                    const nom = groupe.dataset.groupe;

                    // Aucun état mémorisé : on conserve celui rendu par le serveur.
                    if (etats[nom] !== undefined) {
                        groupe.toggleAttribute('open', etats[nom]);
                    }

                    if (groupe.dataset.groupeObserve) return;
                    groupe.dataset.groupeObserve = '1';

                    new MutationObserver(() => {
                        const courant = lire();
                        courant[nom] = groupe.hasAttribute('open');
                        localStorage.setItem(CLE, JSON.stringify(courant));
                    }).observe(groupe, { attributes: true, attributeFilter: ['open'] });
                });
            };

            document.addEventListener('DOMContentLoaded', appliquer);
            // Navigations Livewire : la sidebar est re-rendue sans rechargement.
            document.addEventListener('livewire:navigated', appliquer);
        })();
    </script>

    @stack('scripts')
    @fluxScripts

</body>

</html>
