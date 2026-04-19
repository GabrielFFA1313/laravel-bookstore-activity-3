<?php

namespace App\Providers;

use App\Notifications\EmailVerifiedNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

   public function boot(): void
{
    // Fire notification when email is verified
    Event::listen(Verified::class, function ($event) {
        $event->user->notify(new EmailVerifiedNotification());
    });

    $this->configureRateLimiters(); // ← ADD THIS CALL
}

protected function configureRateLimiters(): void
{
    // ── PASSWORD RESET ────────────────────────────────────────────────────
    RateLimiter::for('password-reset', function (Request $request) {
        return [
            Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip()),
            Limit::perHour(10)->by($request->ip()),
        ];
    });

    // ── PUBLIC ────────────────────────────────────────────────────────────
    RateLimiter::for('public', function (Request $request) {
        return Limit::perMinute(30)
            ->by($request->ip())
            ->response(function (Request $request, array $headers) {
                return response()->json([
                    'message'     => 'Too many requests. Please slow down.',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                    'limit'       => 30,
                ], 429, $headers);
            });
    });

    // ── AUTH ──────────────────────────────────────────────────────────────
    RateLimiter::for('auth', function (Request $request) {
        return Limit::perMinute(10)
            ->by($request->ip())
            ->response(function (Request $request, array $headers) {
                return response()->json([
                    'message'     => 'Too many authentication attempts. Please wait before trying again.',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                    'limit'       => 10,
                ], 429, $headers);
            });
    });

    // ── API (role-based) ──────────────────────────────────────────────────
    RateLimiter::for('api', function (Request $request) {
        $user = $request->user();

        if (!$user) {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message'     => 'Too many requests.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                        'limit'       => 30,
                    ], 429, $headers);
                });
        }

        $limit = match(true) {
            $user->isAdmin()          => 1000,
            $user->role === 'premium' => 300,
            default                   => 60,
        };

        return Limit::perMinute($limit)
            ->by('user:' . $user->id)
            ->response(function (Request $request, array $headers) use ($limit) {
                return response()->json([
                    'message'     => 'Rate limit exceeded.',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                    'limit'       => $limit,
                    'remaining'   => (int) ($headers['X-RateLimit-Remaining'] ?? 0),
                ], 429, $headers);
            });
    });
}
}