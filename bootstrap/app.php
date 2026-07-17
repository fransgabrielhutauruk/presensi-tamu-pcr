<?php

use App\Enums\UserRole;
use App\Http\Middleware\AjaxRequest;
use App\Http\Middleware\CheckActiveRole;
use App\Http\Middleware\EnableCypressMock;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'ajax' => AjaxRequest::class,
            'active-role' => CheckActiveRole::class,
            'setlocale' => SetLocale::class,
        ]);

        $middleware->web(append: [
            EnableCypressMock::class,
            SetLocale::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(fn (Request $request) => UserRole::getDefaultRoute(getActiveRole()));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
