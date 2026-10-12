<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureGates();
    }

    /**
     * Droits transverses qui ne relèvent pas d'une policy de modèle. Le dbcgoq les
     * obtient via Gate::before.
     */
    protected function configureGates(): void
    {
        Gate::define('exporter-cadre-logique', fn ($user) => $user->hasAnyRole(['service-controle-gestion', 'suivi-evaluation']));
        Gate::define('creer-activite-non-programmee', fn ($user) => $user->hasAnyRole(['service-controle-gestion', 'suivi-evaluation']));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Gate::before(function ($user, string $ability) {
            if ($user->hasRole('dbcgoq')) {
                return true;
            }

            return null;
        });

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
