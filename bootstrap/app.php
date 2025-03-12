<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TokenControl;
use App\Http\Middleware\CorsHeader;
use App\Http\Middleware\Killswitch;
use App\Http\Middleware\SecureConnection;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(Killswitch::class);
        $middleware->append(TokenControl::class);
        $middleware->append(CorsHeader::class);
        $middleware->append(SecureConnection::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
