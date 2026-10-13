<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tant que son mot de passe est provisoire, l'utilisateur connecté est renvoyé vers
 * la page de sécurité pour le changer. Restent accessibles cette page, la
 * déconnexion et les échanges Livewire du formulaire.
 */
class ExigerChangementMotDePasse
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->mot_de_passe_provisoire
            && ! $request->routeIs('security.edit', 'logout', 'password.confirm*')
            && ! $request->is('livewire*')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Vous devez changer votre mot de passe provisoire.'], 403)
                : redirect()->route('security.edit');
        }

        return $next($request);
    }
}
