<?php

namespace App\Notifications;

use App\Models\Activite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActiviteArbitrageNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $action  modifiee | supprimee | fusionnee
     * @param  string  $nomActivite  Libellé de l'activité concernée (snapshot)
     * @param  Activite|null  $cible  Entité à consulter (activité modifiée ou consolidée) ;
     *                                 null pour une suppression (l'entité n'existe plus).
     */
    public function __construct(
        public string $action,
        public string $nomActivite,
        public ?string $motif = null,
        public ?string $nomConsolidee = null,
        public ?Activite $cible = null
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

        $cible = $this->lien($notifiable);

        return $mail
            ->action($this->cible ? 'Voir l\'activité' : 'Voir mes activités', url($cible))
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
            'url' => $this->lien($notifiable),
        ];
    }

    /**
     * Lien vers l'entité concernée si elle existe encore et que le destinataire peut la consulter,
     * sinon repli sur la liste des activités (cas d'une suppression).
     */
    private function lien(object $notifiable): string
    {
        if ($this->cible && $notifiable->can('view', $this->cible)) {
            return route('activites.show', $this->cible);
        }

        return route('activites.index');
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
