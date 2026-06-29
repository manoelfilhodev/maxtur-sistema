<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ApiIdempotency;
use App\Http\Middleware\MasterMiddleware;
use App\Http\Middleware\MobilityAppKey;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'app/*',
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'mobility.key' => MobilityAppKey::class,
            'role' => RoleMiddleware::class,
            'master' => MasterMiddleware::class,
            'tenant' => TenantMiddleware::class,
            'ensureMaster' => MasterMiddleware::class,
            'ensureTenant' => TenantMiddleware::class,
            'api.idempotency' => ApiIdempotency::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());

        $exceptions->render(function (ValidationException $exception, $request) {
            if (! $request->is('api/v2/*')) {
                return null;
            }

            return response()->json([
                'ok' => false,
                'message' => 'Dados inválidos.',
                'data' => ['errors' => $exception->errors()],
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, $request) {
            if (! $request->is('api/v2/*')) {
                return null;
            }

            return response()->json(['ok' => false, 'message' => 'Não autenticado.', 'data' => null], 401);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, $request) {
            if (! $request->is('api/v2/*')) {
                return null;
            }

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage() ?: 'Não foi possível processar a solicitação.',
                'data' => null,
            ], $exception->getStatusCode());
        });
    })->create();
