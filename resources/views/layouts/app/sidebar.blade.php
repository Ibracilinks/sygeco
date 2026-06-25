<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="app-shell min-h-screen bg-white dark:bg-zinc-950">
    @php
        $currentUser = auth()->user();
    @endphp

    <flux:sidebar sticky collapsible="mobile"
        class="app-sidebar border-e border-zinc-200 bg-zinc-50/95 dark:border-zinc-800 dark:bg-zinc-900/95">
        <flux:sidebar.header class="app-sidebar-header">
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <div class="app-sidebar-intro hidden lg:block">
            <p class="app-sidebar-intro-kicker">CANAM</p>
            <p class="app-sidebar-intro-title">Centre de pilotage</p>
            <div class="app-sidebar-intro-chip">
                <span>{{ __('Exercice') }}</span>
                <span>{{ request()->get('annee', now()->year) }}</span>
            </div>
        </div>

        <flux:sidebar.nav class="app-sidebar-nav">

            <!-- Dashboard -->
            <flux:sidebar.group :heading="__('Navigation')" class="app-sidebar-group grid">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <!-- Organisation -->
            @if ($currentUser->hasRole('dbcgoq'))
                <flux:sidebar.group :heading="__('Organisation')" class="app-sidebar-group grid">
                    <flux:sidebar.item icon="building-office" href="{{ route('departements.index') }}"
                        :current="request()->routeIs('departements.*')">
                        {{ __('Départements') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="users" href="{{ route('users.index') }}"
                        :current="request()->routeIs('users.*')">
                        {{ __('Utilisateurs') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endif

            <!-- Planification Stratégique -->
            <flux:sidebar.group :heading="__('Planification Stratégique')" class="app-sidebar-group grid">
                @if ($currentUser->hasRole('dbcgoq'))
                    <flux:sidebar.item icon="calendar-days" href="{{ route('exercices.index') }}"
                        :current="request()->routeIs('exercices.*')">
                        {{ __('Exercices') }}
                    </flux:sidebar.item>
                @endif

                @if ($currentUser->hasAnyRole(['dbcgoq', 'chef_departement']))
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

                @if ($currentUser->hasRole('dbcgoq'))
                    <flux:sidebar.item icon="chart-pie" href="{{ route('budget.analysis') }}"
                        :current="request()->routeIs('budget.analysis')">
                        {{ __('Analyse Budgétaire') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="book-open-text" href="{{ route('journal.index') }}"
                        :current="request()->routeIs('journal.*')">
                        {{ __('Journal') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="check-badge" href="{{ route('validations.index') }}"
                        :current="request()->routeIs('validations.*')">
                        {{ __('Validations') }}
                        @php $nbEnAttente = App\Models\Activite::where('statut', 'soumis')->count(); @endphp
                        @if ($nbEnAttente > 0)
                            <flux:badge class="ml-auto">{{ $nbEnAttente }}</flux:badge>
                        @endif
                    </flux:sidebar.item>
                @endif

                <flux:sidebar.item icon="clipboard-document-list" href="{{ route('activites.index') }}"
                    :current="request()->routeIs('activites.index') || request()->routeIs('activites.create') || request()->routeIs('activites.edit') || request()->routeIs('activites.show')">
                    {{ __('Activités') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-bar" href="{{ route('activites.suivi') }}"
                    :current="request()->routeIs('activites.suivi')">
                    {{ __('Suivi des activités') }}
                </flux:sidebar.item>

                @php($nbNonLues = auth()->user()?->unreadNotifications()->count() ?? 0)
                <flux:sidebar.item icon="bell" href="{{ route('notifications.index') }}"
                    :badge="$nbNonLues > 0 ? $nbNonLues : null"
                    :current="request()->routeIs('notifications.*')">
                    {{ __('Notifications') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
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

    @stack('scripts')
    @fluxScripts

</body>

</html>
