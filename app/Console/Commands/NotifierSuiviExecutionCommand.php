<?php

namespace App\Console\Commands;

use App\Models\Exercice;
use App\Models\User;
use App\Notifications\EvaluationOuverteNotification;
use App\Notifications\MiParcoursOuvertNotification;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notification as NotificationInstance;
use Illuminate\Support\Facades\Notification;

class NotifierSuiviExecutionCommand extends Command
{
    protected $signature = 'activites:notifier-suivi {--dry-run : Afficher sans envoyer les notifications}';

    protected $description = "Notifie les chefs de département de l'ouverture des fenêtres de suivi (mi-parcours et évaluation de fin d'exercice).";

    /**
     * Rôles destinataires : ce sont eux qui renseignent l'état d'exécution des activités.
     */
    private const ROLES_CIBLES = ['chef', 'dbcgoq'];

    public function handle(): int
    {
        $exercice = Exercice::query()->where('statut', 'actif')->orderByDesc('annee')->first();

        if (! $exercice) {
            $this->warn('Aucun exercice au statut « actif ».');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->notifierFenetre(
            $exercice,
            ouverte: $exercice->enPeriodeMiParcours(),
            dejaNotifie: (bool) $exercice->mi_parcours_notifiee_le,
            colonneNotifiee: 'mi_parcours_notifiee_le',
            notification: new MiParcoursOuvertNotification($exercice),
            libelle: 'Mi-parcours',
            dryRun: $dryRun,
        );

        $this->notifierFenetre(
            $exercice,
            ouverte: $exercice->enPeriodeEvaluation(),
            dejaNotifie: (bool) $exercice->evaluation_notifiee_le,
            colonneNotifiee: 'evaluation_notifiee_le',
            notification: new EvaluationOuverteNotification($exercice),
            libelle: 'Évaluation',
            dryRun: $dryRun,
        );

        $this->info('Terminé.');

        return self::SUCCESS;
    }

    private function notifierFenetre(
        Exercice $exercice,
        bool $ouverte,
        bool $dejaNotifie,
        string $colonneNotifiee,
        NotificationInstance $notification,
        string $libelle,
        bool $dryRun,
    ): void {
        if (! $ouverte || $dejaNotifie) {
            return;
        }

        $cibles = User::query()->role(self::ROLES_CIBLES);
        $count = (clone $cibles)->count();

        $this->line("{$libelle} (exercice {$exercice->annee}) → {$count} destinataire(s).");

        if ($dryRun) {
            return;
        }

        $cibles->chunkById(200, function ($users) use ($notification) {
            Notification::send($users, $notification);
        });

        $exercice->forceFill([$colonneNotifiee => now()])->save();
    }
}
