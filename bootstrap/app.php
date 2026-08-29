<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: ['0.0.0.0/0', '::/0', '127.0.0.1'],
            headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->header('X-Debug-Error') === 'huenics-inspect') {
                return response()->json([
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 20),
                ], 500);
            }
        });
        $exceptions->shouldRenderJsonWhen(
            fn (\Illuminate\Http\Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
