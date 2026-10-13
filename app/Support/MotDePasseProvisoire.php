<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Mot de passe provisoire, propre à chaque compte : envoyé par e-mail, il doit être
 * changé à la première connexion (middleware ExigerChangementMotDePasse).
 */
class MotDePasseProvisoire
{
    /**
     * Attribue un nouveau mot de passe provisoire et le renvoie en clair, pour l'e-mail.
     */
    public static function attribuer(User $user): string
    {
        $motDePasse = Str::password(12, symbols: false);

        $user->forceFill([
            'password' => $motDePasse,
            'mot_de_passe_provisoire' => true,
        ])->save();

        return $motDePasse;
    }
}
