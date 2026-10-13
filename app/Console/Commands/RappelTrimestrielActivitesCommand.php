<?php

namespace App\Console\Commands;

use App\Models\Activite;
use App\Models\Exercice;
use App\Models\User;
use App\Notifications\RappelTrimestrielActivitesNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RappelTrimestrielActivitesCommand extends Command
{
    protected $signature = 'activites:rappel-trimestriel
        {--date= : Date de référence (AAAA-MM-JJ), aujourd\'hui par défaut}
        {--dry-run : Afficher sans envoyer les notifications}';

    protected $description = 'Rappelle aux comptes de chaque structure ses activités du trimestre en cours non encore réalisées.';

    /**
     * Jours d'envoi dans le dernier mois du trimestre (planifiés dans routes/console.php),
     * et rang du rappel correspondant.
     */
    public const JOURS_DE_RAPPEL = [5 => 1, 12 => 2, 19 => 3, 26 => 4];

    public function handle(): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $trimestre = $date->quarter;
        $rang = self::JOURS_DE_RAPPEL[$date->day] ?? null;

        $exercice = Exercice::query()->where('statut', 'actif')->orderByDesc('annee')->first();

        if (! $exercice) {
            $this->warn('Aucun exercice au statut « actif ».');

            return self::SUCCESS;
        }

        // Le trimestre se lit dans l'année civile : un exercice actif d'une autre année
        // ne correspond à aucun trimestre en cours.
        if ((int) $exercice->annee !== $date->year) {
            $this->warn("L'exercice actif ({$exercice->annee}) ne couvre pas l'année {$date->year}.");

            return self::SUCCESS;
        }

        // Activités validées, programmées sur ce trimestre, sans évaluation « Réalisé ».
        $parStructure = Activite::query()
            ->forExercice($exercice->id)
            ->valide()
            ->pourTrimestre($trimestre)
            ->whereNotNull('departement_id')
            ->whereDoesntHave('evaluations', fn ($q) => $q->where('statut_execution', 'realise'))
            ->orderBy('nom_activite')
            ->get(['id', 'departement_id', 'nom_activite'])
            ->groupBy('departement_id');

        $this->line("T{$trimestre} {$exercice->annee}".($rang ? " — rappel {$rang}/4" : '').' : '
            .$parStructure->flatten()->count().' activité(s) non réalisée(s) dans '.$parStructure->count().' structure(s).');

        $destinataires = 0;

        User::query()
            ->whereIn('departement_id', $parStructure->keys())
            ->chunkById(200, function ($users) use ($parStructure, $exercice, $trimestre, $rang, &$destinataires) {
                foreach ($users as $user) {
                    $activites = $parStructure[$user->departement_id]->pluck('nom_activite')->all();
                    $destinataires++;

                    if ($this->option('dry-run')) {
                        $this->line("[{$user->email}] ".count($activites).' activité(s)');

                        continue;
                    }

                    $user->notify(new RappelTrimestrielActivitesNotification($exercice, $trimestre, $rang, $activites));
                }
            });

        $this->info("Terminé : {$destinataires} destinataire(s).");

        return self::SUCCESS;
    }
}
