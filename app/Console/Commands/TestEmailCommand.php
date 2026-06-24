<?php

namespace App\Console\Commands;

use App\Models\Activite;
use App\Models\User;
use App\Notifications\ActiviteValidee;
use Illuminate\Console\Command;

class TestEmailCommand extends Command
{
    protected $signature = 'mail:test {email : Adresse e-mail de destination}';

    protected $description = 'Envoie la notification « Activité validée » à une adresse pour tester la configuration SMTP';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Adresse e-mail invalide : {$email}");

            return self::FAILURE;
        }

        $activite = Activite::with(['departement', 'extrant'])->latest('id')->first();

        if (! $activite) {
            $this->error('Aucune activité en base : impossible de générer la notification de test.');

            return self::FAILURE;
        }

        // Destinataire de test, non persisté : la notification n'utilise que le canal mail.
        $destinataire = new User(['name' => 'Destinataire Test', 'email' => $email]);

        $this->line('Mailer : ' . config('mail.default'));
        $this->line('Hôte   : ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
        $this->line('Envoi  : ' . config('mail.from.address') . ' → ' . $email);
        $this->line('Activité : ACT-' . $activite->id . ' — ' . $activite->nom_activite);

        try {
            $destinataire->notify(new ActiviteValidee($activite, 'E-mail de test envoyé via la commande mail:test.'));
        } catch (\Throwable $e) {
            $this->error('Échec de l\'envoi : ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Notification « Activité validée » envoyée à {$email}.");

        return self::SUCCESS;
    }
}
