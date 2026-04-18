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
                $user = Auth::guard($guard)->user();
                if ($user !== null) {
                    return $user;
                }
            } catch (\Exception $e) {
                // skip
            }
        }

        return null;
    }
}