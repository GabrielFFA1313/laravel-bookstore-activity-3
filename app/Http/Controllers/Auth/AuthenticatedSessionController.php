<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use App\Notifications\NewDeviceLoginNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Audit\AuditEvent;

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
    // Try to authenticate — catch failure for audit logging
    try {
        $request->authenticate();
    } catch (\Illuminate\Validation\ValidationException $e) {
        AuditEvent::loginFailed($request->email); // only fires on failure
        throw $e;
    }

    // From here, login was successful
    AuditEvent::login(auth()->id());

    $user = Auth::user();

    // 2FA intercept
    if ($user->hasTwoFactorEnabled()) {
        Auth::logout();
        session([
            '2fa:user_id'  => $user->id,
            '2fa:type'     => $user->two_factor_type,
            '2fa:remember' => $request->boolean('remember'),
        ]);
        return redirect()->route('two-factor.challenge');
    }

    $request->session()->regenerate();

    // New device login notification
    $lastIp    = $user->last_login_ip;
    $currentIp = $request->ip();

    if ($lastIp !== $currentIp) {
        $user->notify(new NewDeviceLoginNotification($currentIp, $request->userAgent()));
    }

    $user->forceFill(['last_login_ip' => $currentIp])->save();

    if ($user->isAdmin()) {
        return redirect()->intended(route('admin.dashboard'));
    }

    return redirect()->intended(route('customer.dashboard'));
}

    /**
     * Destroy an authenticated session.
     */
   public function destroy(Request $request): RedirectResponse
{
    $userId = auth()->id(); // ← must be BEFORE logout

    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    AuditEvent::logout($userId); // ← fires after capturing ID

    return redirect('/');
}
}