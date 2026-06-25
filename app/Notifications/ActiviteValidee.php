<?php

namespace App\Notifications;

use App\Models\Activite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActiviteValidee extends Notification
{
    use Queueable;

    public function __construct(protected Activite $activite, protected ?string $commentaire = null) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Activité validée par la DBCGOQ')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre activité "' . $this->activite->nom_activite . '" a été validée.')
            ->line('Département : ' . optional($this->activite->departement)->nom)
            ->line('Extrant : ' . optional($this->activite->extrant)->code)
            ->line('Coût : ' . number_format($this->activite->cout, 0, ',', ' ') . ' FCFA')
            ->when($this->commentaire, function (MailMessage $mail) {
                $mail->line('Commentaire DBCGOQ :')->line($this->commentaire);
            })
            ->action('Voir l\'activité', url(route('activites.show', $this->activite)))
            ->line('Merci pour votre contribution.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'activite_validee',
            'activite_id' => $this->activite->id,
            'action' => 'validée',
            'commentaire' => $this->commentaire,
            'message' => 'Votre activité « ' . $this->activite->nom_activite . ' » a été validée.',
            'url' => route('activites.show', $this->activite),
        ];
    }
}
