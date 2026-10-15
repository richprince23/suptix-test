<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('api/v2')
                ->middleware('api')
                ->group(base_path('routes/api_v2.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->validateCsrfTokens(except: [
            'paystack/webhook',
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })->withSchedule(function (Schedule $schedule) {
        // Critical production commands
        $schedule->command('events:update-statuses')
            ->everyFifteenMinutes()
            // ->withoutOverlapping()
            ->onFailure(function () {
                \Log::error('Failed to update event statuses');
            });

        // Cleanup - delete incomplete orders and pending payments after 15 minutes
        $schedule->command('orders:cleanup-incomplete --timeout=15')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Log::info('Incomplete order cleanup completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Failed to cleanup incomplete orders');
            });

        // Financial reconciliation
        $schedule->command('paystack:sync-balances --all')
            ->everyThirtyMinutes()
            // ->at('08:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                \Log::error('Failed to sync Paystack balances');
            });

        // Queue maintenance
        $schedule->command('queue:prune-failed --hours=48')
            ->daily()
            ->at('03:00');

        $schedule->command('queue:prune-batches --hours=48')
            ->daily()
            ->at('03:30');

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render custom error pages for HTTP exceptions
        // $exceptions->render(function (\Illuminate\Http\Exceptions\HttpResponseException $e, $request) {
        //     return null; // Let Laravel handle HttpResponseException normally
        // });

        // $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
        //     if ($request->expectsJson()) {
        //         return null; // Let API return JSON responses
        //     }

        //     $statusCode = $e->getStatusCode();
        //     $errorViews = [
        //         // 401 => 'errors.401',
        //         403 => 'errors.403',
        //         404 => 'errors.404',
        //         419 => 'errors.419',
        //         // 422 => 'errors.422',
        //         429 => 'errors.429',
        //         500 => 'errors.500',
        //         503 => 'errors.503',
        //     ];

        //     // Use specific error page if available, otherwise use generic
        //     $view = $errorViews[$statusCode] ?? 'errors.generic';

        //     if (view()->exists($view)) {
        //         return response()->view($view, [
        //             'exception' => $e,
        //         ], $statusCode);
        //     }

        //     return null; // Fall back to default Laravel error handling
        // });

        // // Handle CSRF token mismatch specifically
        // $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
        //     if ($request->expectsJson()) {
        //         return response()->json([
        //             'message' => 'CSRF token mismatch.',
        //             'error' => 'token_mismatch'
        //         ], 419);
        //     }

        //     return response()->view('errors.419', [
        //         'exception' => $e,
        //     ], 419);
        // });

        // // Handle throttle exceptions (rate limiting)
        // $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
        //     if ($request->expectsJson()) {
        //         return response()->json([
        //             'message' => 'Too many requests. Please slow down.',
        //             'retry_after' => $e->getHeaders()['Retry-After'] ?? 60
        //         ], 429);
        //     }

        //     return response()->view('errors.429', [
        //         'exception' => $e,
        //     ], 429);
        // });

        // Handle validation exceptions
        // $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
        //     if ($request->expectsJson()) {
        //         return response()->json([
        //             'message' => 'The given data was invalid.',
        //             'errors' => $e->errors()
        //         ], 422);
        //     }

        //     // For non-JSON requests, redirect back with errors (default Laravel behavior)
        //     return null;
        // });

        // Handle authentication exceptions
        // $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
        //     if ($request->expectsJson()) {
        //         return response()->json([
        //             'message' => 'Unauthenticated.',
        //             'error' => 'authentication_required'
        //         ], 401);
        //     }

        //     return response()->view('errors.401', [
        //         'exception' => $e,
        //     ], 401);
        // });

        // Log critical errors
        $exceptions->report(function (\Illuminate\Auth\AuthenticationException $e) {
            \Log::info('Authentication exception: ' . $e->getMessage());
        });

        $exceptions->report(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() >= 500) {
                \Log::error('Server error: ' . $e->getMessage(), [
                    'status_code' => $e->getStatusCode(),
                    'url' => request()->fullUrl(),
                    'user_id' => auth()->id(),
                ]);
            }
        });
    })->create();
