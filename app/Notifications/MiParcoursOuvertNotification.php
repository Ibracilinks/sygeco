<?php

namespace App\Notifications;

use App\Models\Exercice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MiParcoursOuvertNotification extends Notification
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
            ->subject('Suivi à mi-parcours des activités — exercice '.$this->exercice->annee)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("La période de suivi à mi-parcours pour l'exercice {$this->exercice->annee} est ouverte.")
            ->line("Merci de renseigner l'état d'exécution de vos activités (réalisé, en cours ou non réalisé).");

        if ($this->exercice->date_fin_mi_parcours) {
            $mail->line('Date limite : '.$this->exercice->date_fin_mi_parcours->format('d/m/Y').'.');
        }

        return $mail
            ->action('Renseigner le suivi', url(route('activites.suivi')))
            ->line('Merci de votre collaboration.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mi_parcours',
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'date_fin' => $this->exercice->date_fin_mi_parcours?->toDateString(),
            'message' => "Suivi à mi-parcours ouvert pour l'exercice {$this->exercice->annee} : renseignez l'état d'exécution de vos activités.",
        ];
    }
}
