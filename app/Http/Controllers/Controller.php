<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    /**
     * Page d'où vient l'action (filtres, pagination compris), pour y revenir après
     * un traitement ; à défaut, ou si elle figure dans $exclues, $defaut. Seule une
     * page de l'application est retenue.
     *
     * @param  array<int, string>  $exclues
     */
    protected function pagePrecedente(Request $request, string $defaut, array $exclues = []): string
    {
        $referer = (string) $request->headers->get('referer');

        if ($referer === '' || parse_url($referer, PHP_URL_HOST) !== $request->getHost()) {
            return $defaut;
        }

        $chemin = parse_url($referer, PHP_URL_PATH) ?: '/';

        foreach ($exclues as $exclue) {
            if ($chemin === (parse_url($exclue, PHP_URL_PATH) ?: '/')) {
                return $defaut;
            }
        }

        return $referer;
    }
}
