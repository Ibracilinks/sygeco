<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail de bienvenue envoyé à la création d'un compte, contenant les identifiants de connexion.
 *
 * Le mot de passe en clair n'est transmis que par e-mail ; le canal « database » (in-app)
 * ne stocke jamais le mot de passe.
 */
class BienvenueUtilisateurNotification extends Notification
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
            ->subject('Votre compte '.config('app.name').' a été créé')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Un compte vous a été créé sur la plateforme '.config('app.name').' (Plan de Travail Annuel — CANAM).')
            ->line('Voici vos identifiants de connexion :')
            ->line('• Adresse e-mail : '.$notifiable->email)
            ->line('• Mot de passe : '.$this->motDePasse)
            ->action('Se connecter', url(route('login')))
            ->line('Pour votre sécurité, nous vous recommandons de modifier votre mot de passe après la première connexion.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bienvenue',
            'message' => 'Votre compte a été créé. Vos identifiants vous ont été envoyés par e-mail.',
            'url' => route('dashboard'),
        ];
    }
}
