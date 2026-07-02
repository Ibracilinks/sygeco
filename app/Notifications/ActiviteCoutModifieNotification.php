<?php

namespace App\Notifications;

use App\Models\Activite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie le directeur de la Direction Centrale et le chef de service concernés
 * qu'un coût (budget) d'activité a été modifié.
 */
class ActiviteCoutModifieNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $contexte  'edition' (modification en saisie) | 'arbitrage' (arbitrage budgétaire)
     */
    public function __construct(
        protected Activite $activite,
        protected float $ancienCout,
        protected float $nouveauCout,
        protected string $contexte = 'edition',
        protected ?string $motif = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Modification du budget d\'une activité')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->phrase())
            ->line('Structure : ' . optional($this->activite->departement)->nom)
            ->line('Ancien coût : ' . $this->formatCout($this->ancienCout))
            ->line('Nouveau coût : ' . $this->formatCout($this->nouveauCout))
            ->line('Écart : ' . $this->formatEcart());

        if ($this->motif) {
            $mail->line('Motif : ' . $this->motif);
        }

        return $mail
            ->action('Voir l\'activité', url(route('activites.show', $this->activite)))
            ->line($this->contexte === 'arbitrage'
                ? 'Cette modification a été effectuée lors de l\'arbitrage budgétaire.'
                : 'Cette modification a été effectuée sur la fiche de l\'activité.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'activite_cout_modifie',
            'activite_id' => $this->activite->id,
            'action' => 'budget_modifie',
            'contexte' => $this->contexte,
            'ancien_cout' => $this->ancienCout,
            'nouveau_cout' => $this->nouveauCout,
            'motif' => $this->motif,
            'message' => $this->phrase(),
            'url' => route('activites.show', $this->activite),
        ];
    }

    private function phrase(): string
    {
        return "Le budget de l'activité « {$this->activite->nom_activite} » est passé de "
            . $this->formatCout($this->ancienCout) . ' à ' . $this->formatCout($this->nouveauCout) . '.';
    }

    private function formatCout(float $montant): string
    {
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    private function formatEcart(): string
    {
        $ecart = $this->nouveauCout - $this->ancienCout;
        $signe = $ecart >= 0 ? '+' : '-';

        return $signe . ' ' . number_format(abs($ecart), 0, ',', ' ') . ' FCFA';
    }
}
