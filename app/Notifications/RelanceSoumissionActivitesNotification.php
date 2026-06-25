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
            ->line("La Direction du Budget, du Contrôle de Gestion, de l'Organisation et de la Qualité (DBCGOQ) attire votre attention sur l'état d'avancement du Plan de Travail Annuel de l'exercice {$this->exercice->annee}.")
            ->line("À ce jour, votre département compte encore **{$this->nbBrouillons} activité(s) en brouillon** non soumise(s) à validation.")
            ->action('Voir les activités', url(route('activites.index')))
            ->line('Nous vous prions de bien vouloir procéder à leur soumission pour validation dans les meilleurs délais.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'nb_brouillons' => $this->nbBrouillons,
            'message' => "Relance exercice {$this->exercice->annee} : {$this->nbBrouillons} activité(s) à soumettre.",
            'url' => route('activites.index'),
        ];
    }
}
