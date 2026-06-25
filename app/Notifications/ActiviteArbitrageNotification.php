<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActiviteArbitrageNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $action  modifiee | supprimee | fusionnee
     * @param  string  $nomActivite  Libellé de l'activité concernée (snapshot)
     */
    public function __construct(
        public string $action,
        public string $nomActivite,
        public ?string $motif = null,
        public ?string $nomConsolidee = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->sujet())
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($this->phrase());

        if ($this->motif) {
            $mail->line('Motif : '.$this->motif);
        }

        return $mail
            ->action('Voir mes activités', url(route('activites.index')))
            ->line("Cette décision a été prise lors de l'arbitrage budgétaire, avant validation.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->action,
            'nom_activite' => $this->nomActivite,
            'nom_consolidee' => $this->nomConsolidee,
            'motif' => $this->motif,
            'message' => $this->phrase(),
            'url' => route('activites.index'),
        ];
    }

    private function sujet(): string
    {
        return match ($this->action) {
            'supprimee' => 'Arbitrage — activité supprimée',
            'fusionnee' => 'Arbitrage — activité fusionnée',
            default => 'Arbitrage — activité modifiée',
        };
    }

    private function phrase(): string
    {
        return match ($this->action) {
            'supprimee' => "Votre activité « {$this->nomActivite} » a été supprimée lors de l'arbitrage budgétaire.",
            'fusionnee' => "Votre activité « {$this->nomActivite} » a été fusionnée".($this->nomConsolidee ? " dans « {$this->nomConsolidee} »" : '').' lors de l\'arbitrage budgétaire.',
            default => "Votre activité « {$this->nomActivite} » a été modifiée lors de l'arbitrage budgétaire.",
        };
    }
}
