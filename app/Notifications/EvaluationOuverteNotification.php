<?php

namespace App\Notifications;

use App\Models\Exercice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationOuverteNotification extends Notification
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
            ->subject("Évaluation de fin d'exercice — exercice ".$this->exercice->annee)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("La période d'évaluation de fin d'exercice pour l'exercice {$this->exercice->annee} est ouverte.")
            ->line("Merci de finaliser l'état d'exécution de vos activités (réalisé, en cours ou non réalisé).");

        if ($this->exercice->date_fin_evaluation) {
            $mail->line('Date limite : '.$this->exercice->date_fin_evaluation->format('d/m/Y').'.');
        }

        return $mail
            ->action('Renseigner le bilan', url(route('activites.suivi')))
            ->line('Merci de votre collaboration.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'evaluation',
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'date_fin' => $this->exercice->date_fin_evaluation?->toDateString(),
            'message' => "Évaluation de fin d'exercice ouverte pour l'exercice {$this->exercice->annee} : finalisez l'état d'exécution de vos activités.",
            'url' => route('activites.suivi'),
        ];
    }
}
