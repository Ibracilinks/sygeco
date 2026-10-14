<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
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
        $this->configureMails();
    }

    /**
     * Mail « mot de passe oublié » (Fortify) en français, dans le ton des autres mails.
     */
    protected function configureMails(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Réinitialisation de votre mot de passe '.config('app.name'))
                ->greeting('Bonjour '.$notifiable->name.',')
                ->line('Vous recevez ce message car une demande de réinitialisation du mot de passe a été faite pour votre compte.')
                ->action('Réinitialiser le mot de passe', $url)
                ->line("Ce lien expire dans {$minutes} minutes.")
                ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez ce message : votre mot de passe reste inchangé.");
        });
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

        // 8 caractères minimum, sans exigence de majuscule, de chiffre ni de caractère spécial.
        Password::defaults(fn (): Password => Password::min(8));
    }
}
