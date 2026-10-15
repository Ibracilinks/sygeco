<?php

namespace App\Notifications;

use App\Models\Activite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Activité enregistrée par un chef de service : son responsable de programme,
 * seul habilité à la soumettre, en est informé.
 */
class ActiviteSaisieNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Activite $activite
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle activité à soumettre — '.optional($this->activite->departement)->nom)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line(optional($this->activite->saisiePar)->name.' a enregistré une activité de son service. Elle attend votre soumission.')
            ->line('Activité : '.$this->activite->nom_activite)
            ->line('Structure : '.optional($this->activite->departement)->nom)
            ->line('Extrant : '.optional($this->activite->extrant)->code)
            ->line('Coût : '.($this->activite->pour_memoire ? 'pour mémoire' : number_format((float) $this->activite->cout, 0, ',', ' ').' FCFA'))
            ->action('Voir l\'activité', route('activites.show', $this->activite));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'activite_saisie',
            'activite_id' => $this->activite->id,
            'departement' => optional($this->activite->departement)->nom,
            'message' => 'Activité à soumettre : « '.$this->activite->nom_activite.' » ('.optional($this->activite->departement)->nom.'), saisie par '.optional($this->activite->saisiePar)->name.'.',
            'url' => route('activites.show', $this->activite),
        ];
    }
}
