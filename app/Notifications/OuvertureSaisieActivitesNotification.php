<?php

namespace App\Notifications;

use App\Models\Exercice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OuvertureSaisieActivitesNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Exercice $exercice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Ouverture de la saisie des activités — exercice '.$this->exercice->annee)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("La saisie des activités pour l'exercice {$this->exercice->annee} est désormais ouverte.");

        if ($this->exercice->date_limite_saisie) {
            $mail->line('Date limite de saisie : '.$this->exercice->date_limite_saisie->format('d/m/Y').'.');
        }

        return $mail
            ->action('Renseigner mes activités', url(route('activites.index')))
            ->line('Merci de renseigner vos activités avant la date limite.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'date_limite_saisie' => $this->exercice->date_limite_saisie?->toDateString(),
            'message' => "Ouverture de la saisie des activités pour l'exercice {$this->exercice->annee}.",
        ];
    }
}
