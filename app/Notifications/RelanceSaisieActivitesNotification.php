<?php

namespace App\Notifications;

use App\Models\Exercice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RelanceSaisieActivitesNotification extends Notification
{
    use Queueable;

    /**
     * @param  int  $palier  Nombre de jours restant avant la date limite (0 = jour J).
     */
    public function __construct(
        public Exercice $exercice,
        public int $palier
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $limite = $this->exercice->date_limite_saisie?->format('d/m/Y');
        $jours = max(0, $this->exercice->joursAvantLimite() ?? $this->palier);

        $mail = (new MailMessage)
            ->subject($this->sujet().' — exercice '.$this->exercice->annee)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("Dans le cadre de l'élaboration du Plan de Travail Annuel (PTA) de l'exercice {$this->exercice->annee}, la Direction du Budget, du Contrôle de Gestion, de l'Organisation et de la Qualité (DBCGOQ) vous rappelle que la saisie de vos activités est en cours.");

        if ($jours === 0) {
            $mail->line("**Aujourd'hui est le dernier jour** pour renseigner vos activités".($limite ? " (date limite : {$limite})" : '').'.');
        } else {
            $mail->line("Il vous reste **{$jours} jour(s)** pour finaliser cette saisie".($limite ? " (date limite : {$limite})" : '').'.');
        }

        return $mail
            ->action('Renseigner mes activités', url($this->lien($notifiable)))
            ->line('Nous vous remercions de bien vouloir finaliser la saisie de vos activités avant l\'échéance afin de garantir la consolidation du PTA dans les délais impartis.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'palier' => $this->palier,
            'date_limite_saisie' => $this->exercice->date_limite_saisie?->toDateString(),
            'message' => $this->sujet()." (exercice {$this->exercice->annee}).",
            'url' => $this->lien($notifiable),
        ];
    }

    /**
     * Le DBCGOQ ouvre la fiche de l'exercice ; les autres comptes, leur écran de saisie.
     */
    private function lien(object $notifiable): string
    {
        return $notifiable->hasRole('dbcgoq')
            ? route('exercices.show', $this->exercice)
            : route('activites.index');
    }

    private function sujet(): string
    {
        return $this->palier === 0
            ? 'Dernier jour pour la saisie des activités'
            : "Relance — saisie des activités (J-{$this->palier})";
    }
}
