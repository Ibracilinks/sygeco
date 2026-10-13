<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mot de passe réinitialisé par un administrateur : le mot de passe provisoire n'est
 * transmis que par e-mail, jamais stocké dans la notification in-app.
 */
class MotDePasseReinitialiseNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $motDePasse
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre mot de passe '.config('app.name').' a été réinitialisé')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre mot de passe sur la plateforme '.config('app.name').' a été réinitialisé par un administrateur.')
            ->line('• Adresse e-mail : '.$notifiable->email)
            ->line('• Mot de passe provisoire : '.$this->motDePasse)
            ->action('Se connecter', url(route('login')))
            ->line('À votre prochaine connexion, il vous sera demandé de le remplacer par un mot de passe personnel.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, prévenez l'équipe informatique.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mot_de_passe_reinitialise',
            'message' => 'Votre mot de passe a été réinitialisé. Le mot de passe provisoire vous a été envoyé par e-mail.',
            'url' => route('security.edit'),
        ];
    }
}
