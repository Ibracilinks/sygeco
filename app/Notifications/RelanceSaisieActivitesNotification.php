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
            ->greeting('Bonjour '.$notifiable->name.',');

        if ($jours === 0) {
            $mail->line("Dernier jour pour renseigner vos activités de l'exercice {$this->exercice->annee}".($limite ? " (date limite : {$limite})" : '').'.');
        } else {
            $mail->line("Il reste {$jours} jour(s) pour renseigner vos activités de l'exercice {$this->exercice->annee}".($limite ? " (date limite : {$limite})" : '').'.');
        }

        return $mail
            ->action('Renseigner mes activités', url(route('activites.index')))
            ->line('Merci de finaliser la saisie de vos activités dans les délais.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'palier' => $this->palier,
            'date_limite_saisie' => $this->exercice->date_limite_saisie?->toDateString(),
            'message' => $this->sujet()." (exercice {$this->exercice->annee}).",
        ];
    }

    private function sujet(): string
    {
        return $this->palier === 0
            ? 'Dernier jour pour la saisie des activités'
            : "Relance — saisie des activités (J-{$this->palier})";
    }
}
