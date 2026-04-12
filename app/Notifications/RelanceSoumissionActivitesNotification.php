<?php

namespace App\Notifications;

use App\Models\Exercice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RelanceSoumissionActivitesNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Exercice $exercice,
        public int $nbBrouillons
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Relance — soumission des activités (exercice '.$this->exercice->annee.')')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("L'exercice {$this->exercice->annee} compte encore {$this->nbBrouillons} activité(s) en brouillon pour votre département.")
            ->action('Voir les activités', url(route('activites.index')))
            ->line('Merci de les soumettre pour validation.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'nb_brouillons' => $this->nbBrouillons,
            'message' => "Relance exercice {$this->exercice->annee} : {$this->nbBrouillons} activité(s) à soumettre.",
        ];
    }
}
