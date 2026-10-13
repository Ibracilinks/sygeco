<?php

namespace App\Notifications;

use App\Models\Exercice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RappelTrimestrielActivitesNotification extends Notification
{
    use Queueable;

    /** Nombre d'intitulés repris dans le mail ; au-delà, on renvoie à la liste. */
    private const ACTIVITES_DANS_LE_MAIL = 10;

    /**
     * @param  int  $trimestre  Trimestre concerné (1 à 4).
     * @param  int|null  $rang  Rang du rappel dans le mois (1 à 4), null pour un envoi manuel.
     * @param  array<int, string>  $activites  Intitulés des activités non réalisées.
     */
    public function __construct(
        public Exercice $exercice,
        public int $trimestre,
        public ?int $rang,
        public array $activites,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $nombre = count($this->activites);

        $mail = (new MailMessage)
            ->subject($this->sujet().' — exercice '.$this->exercice->annee)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("Le trimestre T{$this->trimestre} {$this->exercice->annee} touche à sa fin. {$nombre} activité(s) validée(s) de votre structure, programmée(s) sur ce trimestre, ne sont pas encore marquées « Réalisé » :");

        foreach (array_slice($this->activites, 0, self::ACTIVITES_DANS_LE_MAIL) as $nom) {
            $mail->line('• '.$nom);
        }

        if ($nombre > self::ACTIVITES_DANS_LE_MAIL) {
            $mail->line('… et '.($nombre - self::ACTIVITES_DANS_LE_MAIL).' autre(s).');
        }

        return $mail
            ->action('Voir les activités du trimestre', $this->lien())
            ->line("Merci de veiller à leur exécution et d'en renseigner l'état dans LEAC.");
    }

    public function toArray(object $notifiable): array
    {
        $nombre = count($this->activites);

        return [
            'exercice_id' => $this->exercice->id,
            'annee' => $this->exercice->annee,
            'trimestre' => $this->trimestre,
            'rang' => $this->rang,
            'nombre' => $nombre,
            'message' => $this->sujet()." : {$nombre} activité(s) du T{$this->trimestre} pas encore réalisée(s).",
            'url' => $this->lien(),
        ];
    }

    /**
     * Liste de programmation filtrée sur le trimestre et les activités validées :
     * accessible à tous les profils, y compris ceux qui n'évaluent pas.
     */
    private function lien(): string
    {
        return route('activites.index', ['trimestre' => $this->trimestre, 'statut' => 'valide']);
    }

    private function sujet(): string
    {
        $base = "Fin du trimestre T{$this->trimestre} — activités à réaliser";

        return $this->rang ? "{$base} (rappel {$this->rang}/4)" : $base;
    }
}
