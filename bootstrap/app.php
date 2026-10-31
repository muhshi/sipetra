<?php

use App\Http\Middleware\NoCacheHeaders;
use App\Http\Middleware\ValidateMasterToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo('/login');
        $middleware->appendToGroup('web', [
            NoCacheHeaders::class,
        ]);

        // Alias untuk validasi token M2M Master Data API
        $middleware->alias([
            'master.token' => ValidateMasterToken::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Tangkap semua error HTTP
        $exceptions->render(function (HttpException $e, Request $request) {

            // Jika errornya Forbidden (403) dan user mencoba menjebol dari URL `/admin...`
            if ($e->getStatusCode() === 403 && $request->is('admin*')) {
                // Paksa memantul kembali ke beranda portal
                return redirect()->route('home')
                    ->with('error', 'Maaf, Anda tidak memiliki hak akses untuk masuk ke panel Administrator.');
            }
        });
    })->create();
