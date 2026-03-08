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
        // Rate limiter for password reset
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });

        // Fire notification when email is verified
        Event::listen(Verified::class, function ($event) {
            $event->user->notify(new EmailVerifiedNotification());
        });
    }
}