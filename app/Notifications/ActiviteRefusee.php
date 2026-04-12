<?php

namespace App\Notifications;

use App\Models\Activite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActiviteRefusee extends Notification
{
    use Queueable;

    public function __construct(protected Activite $activite, protected string $motif) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Activité refusée par la DBCGOQ')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre activité "' . $this->activite->nom_activite . '" a été refusée.')
            ->line('Motif du refus :')
            ->line($this->motif)
            ->line('Département : ' . optional($this->activite->departement)->nom)
            ->action('Voir l\'activité', url(route('activites.show', $this->activite)))
            ->line('Merci de corriger et de soumettre à nouveau si nécessaire.');
    }

    public function toArray($notifiable)
    {
        return [
            'activite_id' => $this->activite->id,
            'action' => 'refusée',
            'motif' => $this->motif,
        ];
    }
}
