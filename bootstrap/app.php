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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'two-factor' => \App\Http\Middleware\EnsureTwoFactorVerified::class,
            'camelcase'       => \App\Http\Middleware\CamelCaseResponse::class,
            'filter.fields'   => \App\Http\Middleware\FilterFields::class,
            'etag'            => \App\Http\Middleware\ETagCache::class,
            'rate.headers'    => \App\Http\Middleware\AddRateLimitHeaders::class,
        ]);
        $middleware->api(append: [
        \App\Http\Middleware\CamelCaseResponse::class,
        \App\Http\Middleware\FilterFields::class,
        \App\Http\Middleware\ETagCache::class,
        \App\Http\Middleware\AddRateLimitHeaders::class,
]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
