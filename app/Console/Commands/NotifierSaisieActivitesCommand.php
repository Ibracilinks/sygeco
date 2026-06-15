<?php

namespace App\Console\Commands;

use App\Models\Exercice;
use App\Models\ExerciceRelance;
use App\Models\User;
use App\Notifications\OuvertureSaisieActivitesNotification;
use App\Notifications\RelanceSaisieActivitesNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifierSaisieActivitesCommand extends Command
{
    protected $signature = 'activites:notifier-saisie {--dry-run : Afficher sans envoyer les notifications}';

    protected $description = "Notifie tous les comptes de l'ouverture de la saisie et des relances par paliers (J-15 → J)";

    /**
     * Paliers de relance, en jours restant avant la date limite (ordre décroissant, 0 = jour J).
     */
    private const PALIERS = [15, 10, 7, 5, 3, 2, 1, 0];

    public function handle(): int
    {
        $exercice = Exercice::query()->where('statut', 'actif')->orderByDesc('annee')->first();

        if (! $exercice) {
            $this->warn('Aucun exercice au statut « actif ».');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->notifierOuverture($exercice, $dryRun);
        $this->notifierRelances($exercice, $dryRun);

        $this->info('Terminé.');

        return self::SUCCESS;
    }

    private function notifierOuverture(Exercice $exercice, bool $dryRun): void
    {
        if (! $exercice->date_ouverture_saisie || $exercice->ouverture_notifiee_le) {
            return;
        }

        if ($exercice->date_ouverture_saisie->startOfDay()->isFuture()) {
            return;
        }

        $count = User::query()->count();
        $this->line("Ouverture de la saisie (exercice {$exercice->annee}) → {$count} compte(s).");

        if ($dryRun) {
            return;
        }

        $this->envoyerATous(new OuvertureSaisieActivitesNotification($exercice));

        $exercice->forceFill(['ouverture_notifiee_le' => now()])->save();
    }

    private function notifierRelances(Exercice $exercice, bool $dryRun): void
    {
        $jours = $exercice->joursAvantLimite();

        if ($jours === null || $jours < 0) {
            return;
        }

        $dejaEnvoyes = $exercice->relances()->pluck('palier')->all();

        // Paliers « dus » : tout palier dont la fenêtre est atteinte (palier >= jours restant) et non encore traité.
        $dus = collect(self::PALIERS)
            ->filter(fn (int $p) => $p >= $jours && ! in_array($p, $dejaEnvoyes, true))
            ->sort()
            ->values();

        if ($dus->isEmpty()) {
            return;
        }

        // On n'envoie qu'un seul mail (le palier le plus urgent) ; les paliers manqués sont marqués sans renvoi.
        $cible = $dus->first();

        $count = User::query()->count();
        $this->line("Relance J-{$cible} (jours restant : {$jours}, exercice {$exercice->annee}) → {$count} compte(s).");

        if ($dryRun) {
            return;
        }

        // Marquer les paliers plus anciens manqués (sans renvoyer de mail).
        foreach ($dus as $p) {
            if ($p === $cible) {
                continue;
            }
            ExerciceRelance::create([
                'exercice_id' => $exercice->id,
                'palier' => $p,
                'destinataires' => 0,
                'envoye_le' => null,
            ]);
        }

        $this->envoyerATous(new RelanceSaisieActivitesNotification($exercice, $cible));

        ExerciceRelance::create([
            'exercice_id' => $exercice->id,
            'palier' => $cible,
            'destinataires' => $count,
            'envoye_le' => now(),
        ]);
    }

    private function envoyerATous(object $notification): void
    {
        User::query()->chunkById(200, function ($users) use ($notification) {
            Notification::send($users, $notification);
        });
    }
}
