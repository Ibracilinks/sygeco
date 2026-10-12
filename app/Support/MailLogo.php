<?php

namespace App\Support;

use Illuminate\Mail\Message;
use Symfony\Component\Mime\Part\DataPart;

/**
 * Logo CANAM des mails, intégré en pièce jointe en ligne (CID) : une image distante
 * est bloquée par la plupart des clients mail, et `APP_URL` n'est pas toujours
 * joignable depuis l'extérieur.
 */
class MailLogo
{
    public const FICHIER = 'logo_canam_mail.png';

    /**
     * Source de l'image à placer dans le mail. Le gabarit est rendu deux fois (HTML
     * puis texte) avec le même message : le logo n'est joint qu'une seule fois.
     */
    public static function src(mixed $message): string
    {
        if (! $message instanceof Message) {
            // Aperçu d'un mail hors envoi : pas de message à enrichir.
            return asset('logo_canam.png');
        }

        foreach ($message->getSymfonyMessage()->getAttachments() as $piece) {
            if ($piece instanceof DataPart && $piece->getFilename() === self::FICHIER) {
                return 'cid:'.$piece->getContentId();
            }
        }

        return $message->embed(resource_path('images/'.self::FICHIER));
    }
}
