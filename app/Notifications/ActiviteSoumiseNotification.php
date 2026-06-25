<?php

namespace App\Notifications;

use App\Models\Activite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActiviteSoumiseNotification extends Notification
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
            ->subject('Nouvelle activité soumise pour validation — '.optional($this->activite->departement)->nom)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Une activité vient d\'être soumise et attend votre validation.')
            ->line('Activité : '.$this->activite->nom_activite)
            ->line('Département : '.optional($this->activite->departement)->nom)
            ->line('Extrant : '.optional($this->activite->extrant)->code)
            ->line('Coût : '.number_format((float) $this->activite->cout, 0, ',', ' ').' FCFA')
            ->action('Examiner l\'activité', url($this->lien($notifiable)))
            ->line('Merci de procéder à la validation ou au refus dans les meilleurs délais.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'activite_soumise',
            'activite_id' => $this->activite->id,
            'departement' => optional($this->activite->departement)->nom,
            'message' => 'Activité soumise pour validation : « '.$this->activite->nom_activite.' » ('.optional($this->activite->departement)->nom.').',
            'url' => $this->lien($notifiable),
        ];
    }

    /**
     * Lien vers l'entité, adapté à l'accès du destinataire :
     * le DBCGOQ ouvre l'écran de validation, les chefs ouvrent la fiche de l'activité.
     */
    private function lien(object $notifiable): string
    {
        return $notifiable->hasRole('dbcgoq')
            ? route('validations.show', $this->activite)
            : route('activites.show', $this->activite);
    }
}
