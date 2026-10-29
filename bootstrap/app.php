<?php

use Illuminate\Auth\AuthenticationException;
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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
      
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $e) =>
                $request->is('api/*') || $request->expectsJson()
        );

       
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ['message' => 'غير مصرح. يجب تسجيل الدخول أولاً.'],
                    401
                );
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if (
                $e->getStatusCode() === 422
                && ($request->is('api/*') || $request->expectsJson())
            ) {
                $message = $e->getMessage() ?: 'البيانات المقدمة غير صالحة.';

                return response()->json(['message' => $message], 422);
            }
        });
    })->create();
