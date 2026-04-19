<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AddRateLimitHeaders
{
    public function handle(Request $request, Closure $next, string $limiterName = 'api'): Response
    {
        $user = $request->user();
        $key  = $user ? 'user:' . $user->id : $request->ip();

        $limit     = RateLimiter::attempts($key) ?? 0;
        $maxAttempts = match(true) {
            $user?->isAdmin()          => 1000,
            $user?->role === 'premium' => 300,
            $user !== null             => 60,
            default                    => 30,
        };

        $remaining = max(0, $maxAttempts - $limit);
        $retryAfter = RateLimiter::availableIn($key);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit'     => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset'     => now()->addSeconds($retryAfter)->timestamp,
        ]);
    }
}