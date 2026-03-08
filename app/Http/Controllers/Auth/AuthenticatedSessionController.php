<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // *** 2FA intercept ***
        // If the user has 2FA enabled, log them out temporarily
        // and store their ID in session until they verify the code
        if ($user->hasTwoFactorEnabled()) {
            Auth::logout();

            session([
                '2fa:user_id'  => $user->id,
                '2fa:type'     => $user->two_factor_type,
                '2fa:remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('two-factor.challenge');
        }

        // No 2FA — continue normal login
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}