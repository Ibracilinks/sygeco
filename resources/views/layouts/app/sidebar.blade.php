<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <!-- Dashboard -->
            <flux:sidebar.group :heading="__('Navigation')" class="grid">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <!-- Organisation -->
            <flux:sidebar.group :heading="__('Organisation')" class="grid">
                <flux:sidebar.item icon="building-office" href="{{ route('departements.index') }}"
                    :current="request()->routeIs('departements.*')">
                    {{ __('Départements') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="users" href="{{ route('users.index') }}"
                    :current="request()->routeIs('users.*')">
                    {{ __('Utilisateurs') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <!-- Planification Stratégique -->
            <flux:sidebar.group :heading="__('Planification Stratégique')" class="grid">
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

                <flux:sidebar.item icon="clipboard-document-list" href="{{ route('activites.index') }}"
                    :current="request()->routeIs('activites.*')">
                    {{ __('Activités') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <!-- Gestion Terrain -->
            <flux:sidebar.group :heading="__('Gestion Terrain')" class="grid">
                <flux:sidebar.item icon="pencil-square" href="#" :current="false">
                    {{ __('Mes Activités') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-bar" href="{{ route('indicateurs.index') }}"
                    :current="request()->routeIs('indicateurs.*')">
                    {{ __('Indicateurs') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <!-- Pilotage & Reporting -->
            <flux:sidebar.group :heading="__('Pilotage')" class="grid">
                <flux:sidebar.item icon="document-text" href="#" :current="false">
                    {{ __('Tableaux de Bord') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="home" href="#" :current="false">
                    {{ __('Génération Rapports') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-pie" href="#" :current="false">
                    {{ __('Analyse Budgétaire') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

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

</html
