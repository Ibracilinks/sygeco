<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="auth-shell min-h-screen antialiased">
        <div class="auth-shell-inner mx-auto grid min-h-svh w-full max-w-7xl gap-6 p-5 md:p-8 lg:grid-cols-2">
            <section class="auth-brand-panel hidden rounded-3xl p-10 lg:flex lg:flex-col lg:justify-between">
                <div class="space-y-8">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3" wire:navigate>
                        <picture>
                            <source srcset="{{ asset('logo_canam.png') }}" type="image/png">
                            <img src="{{ asset('logo_canam.jpg') }}" alt="{{ config('app.name', 'CANAM') }}" class="auth-logo h-14 w-auto" loading="lazy">
                        </picture>
                        <span class="auth-brand-name text-2xl font-semibold tracking-wide">{{ config('app.name', 'CANAM') }}</span>
                    </a>

                    <div class="max-w-md space-y-3">
                        <h1 class="text-4xl font-semibold leading-tight text-white">Plateforme de suivi, claire et securisee.</h1>
                        <p class="text-sm leading-6 text-white/85">
                            Connectez-vous pour piloter les activites, objectifs et indicateurs avec une experience unifiee.
                        </p>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center">
                <div class="auth-form-wrap w-full max-w-md rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-2xl shadow-slate-900/10 backdrop-blur-sm md:p-8 dark:border-slate-700/80 dark:bg-slate-900/90 dark:shadow-black/30">
                    <a href="{{ route('home') }}" class="mb-7 flex flex-col items-center gap-3 lg:hidden" wire:navigate>
                        <picture>
                            <source srcset="{{ asset('logo_canam.png') }}" type="image/png">
                            <img src="{{ asset('logo_canam.jpg') }}" alt="{{ config('app.name', 'CANAM') }}" class="auth-logo h-14 w-auto" loading="lazy">
                        </picture>
                        <span class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-600 dark:text-slate-200">{{ config('app.name', 'CANAM') }}</span>
                    </a>

                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </section>
        </div>
        @fluxScripts
    </body>
</html>
