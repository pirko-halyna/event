<?php

use App\Http\Middleware\AuthTokenMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'v1'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->throttleWithRedis();
        $middleware->alias([
            'auth.token' => AuthTokenMiddleware::class,
        ]);
    })
    ->withEvents(discover: [
        __DIR__ . '/../app/Listeners',
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(
            fn (JWTException $exception) => response()->json(['error' => $exception->getMessage()], 401)
        );

        $exceptions->render(
            fn (NotFoundHttpException $exception) => response()->json(['message' => 'Not found'], 404)
        );

        $exceptions->context(fn () => [
            'user_id' => auth()->id() ?? null,
        ]);
        Integration::handles($exceptions);
    })
    ->create();
