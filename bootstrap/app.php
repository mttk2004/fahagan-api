<?php

use App\Http\Middleware\EnsureCustomerMiddleware;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureEmployeeMiddleware;
use App\Http\Middleware\EnsureUserMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
            'auth.*' => EnsureUserMiddleware::class,
            'auth.customer' => EnsureCustomerMiddleware::class,
            'auth.employee' => EnsureEmployeeMiddleware::class,
        ]);

        // Disable CSRF token validation for API routes.
        // Because Sanctum is stateful, CSRF tokens are not needed.
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
