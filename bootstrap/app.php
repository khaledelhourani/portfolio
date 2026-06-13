<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            App\Http\Middleware\SetLocale::class,
            App\Http\Middleware\TrackVisitor::class,
        ]);

        $middleware->alias([
            'cms.unlocked' => App\Http\Middleware\EnsureCmsUnlocked::class,
            'role' => App\Http\Middleware\EnsureAdminRole::class,
            'block.viewer.writes' => App\Http\Middleware\BlockViewerWrites::class,
        ]);

        // Unauthenticated users are sent to the right login: admin area → admin
        // login, public/member routes → member (social) login.
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin*')
            ? route('admin.login')
            : route('member.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
