<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Modules\Product\Application\Exceptions\ProductValidationException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) return null;
            return response()->json(['type' => 'https://ecommercesolution.test/problems/validation', 'title' => 'The request is not valid.', 'status' => 422, 'errors' => $e->errors()], 422, ['Content-Type' => 'application/problem+json']);
        });
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'type' => 'https://ecommercesolution.test/problems/validation',
                'title' => 'The request is not valid.',
                'status' => 422,
                'errors' => $e->errors(),
            ], 422, [
                'Content-Type' => 'application/problem+json',
            ]);
        });
    })->create();
