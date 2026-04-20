<?php

namespace App\Audit;

use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\UserResolver as UserResolverContract;

class UserResolver implements UserResolverContract
{
   public static function resolve()
{
    foreach (config('audit.user.guards', ['web']) as $guard) {
        try {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                // Log what we're returning for debugging
                \Illuminate\Support\Facades\Log::info('UserResolver returning', [
                    'class' => get_class($user),
                    'id'    => $user?->id,
                ]);
                return $user;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('UserResolver error', ['error' => $e->getMessage()]);
            continue;
        }
    }
    return null;
}
}