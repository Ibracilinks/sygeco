<?php

namespace App\Notifications;

use App\Models\Exercice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * À l'ouverture d'un exercice, renvoie à l'utilisateur un nouveau mot de passe et le lien
 * de connexion. Le mot de passe en clair n'est transmis que par e-mail.
 */
class IdentifiantsExerciceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Exercice $exercice,
        public string $motDePasse
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Ouverture de l'exercice {$this->exercice->annee} — vos accès ".config('app.name'))
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("L'exercice {$this->exercice->annee} est ouvert. Voici vos nouveaux identifiants de connexion :")
            ->line('• Adresse e-mail : '.$notifiable->email)
            ->line('• Mot de passe : '.$this->motDePasse);

        if ($this->exercice->date_limite_saisie) {
            $mail->line('Date limite de saisie des activités : '.$this->exercice->date_limite_saisie->format('d/m/Y').'.');
        }

        return $mail
            ->action('Se connecter', url(route('login')))
            ->line('Pour votre sécurité, pensez à modifier votre mot de passe après connexion.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'identifiants_exercice',
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'message' => "Ouverture de l'exercice {$this->exercice->annee} : vos nouveaux identifiants vous ont été envoyés par e-mail.",
            'url' => route('login'),
        ];
    }
}
