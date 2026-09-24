<?php

namespace App\Console\Commands;

use App\Models\Exercice;
use App\Models\User;
use App\Notifications\RelanceSoumissionActivitesNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RelanceSoumissionActivitesCommand extends Command
{
    protected $signature = 'activites:relance-soumission {--dry-run : Afficher sans envoyer les notifications}';

    protected $description = 'Envoie une relance aux chefs de département ayant des activités en brouillon (exercice actif)';

    public function handle(): int
    {
        if (! $this->option('dry-run')) {
            $last = Cache::get('relance_soumission_activites_last_at');
            if ($last && now()->diffInHours($last) < 72) {
                $this->comment('Relance ignorée : dernière exécution il y a moins de 72 h.');

                return self::SUCCESS;
            }
        }

        $exercice = Exercice::query()->where('statut', 'actif')->orderByDesc('annee')->first();

        if (! $exercice) {
            $this->warn('Aucun exercice au statut « actif ».');

            return self::SUCCESS;
        }

        $users = User::query()->role('responsable-programme')->whereNotNull('departement_id')->get();

        foreach ($users as $user) {
            $count = $user->departement
                ? $user->departement->activites()
                    ->forExercice($exercice->id)
                    ->where('statut', 'brouillon')
                    ->count()
                : 0;

            if ($count === 0) {
                continue;
            }

            $this->line("[{$user->email}] {$count} activité(s) en brouillon");

            if ($this->option('dry-run')) {
                continue;
            }

            $user->notify(new RelanceSoumissionActivitesNotification($exercice, $count));
        }

        if (! $this->option('dry-run')) {
            Cache::put('relance_soumission_activites_last_at', now(), now()->addDays(14));
        }

        $this->info('Terminé.');

        return self::SUCCESS;
    }
}
