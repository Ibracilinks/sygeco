<?php

use App\Http\Middleware\ExigerChangementMotDePasse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Le TLS est terminé par Cloudflare : seul le schéma annoncé par le proxy est
        // repris, pour générer des URLs https://. L'hôte transmis n'est pas pris en
        // compte (il servirait à forger les liens des e-mails de réinitialisation).
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_PROTO);

        $middleware->web(append: [
            ExigerChangementMotDePasse::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
