<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\TwoFactorEnabledNotification;
use App\Notifications\TwoFactorDisabledNotification;
use App\Notifications\NewDeviceLoginNotification;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    // LOGIN FLOW
    // Show the 2FA challenge page during login
    public function challenge()
    {
        if (! session()->has('2fa:user_id')) {
            return redirect()->route('login');
        }

        $type = session('2fa:type', 'email');

        // Auto-send OTP if email type
        if ($type === 'email') {
            $this->sendEmailOtp(session('2fa:user_id'));
        }

        return view('auth.two-factor-challenge', compact('type'));
    }

    // Verify the submitted code during login
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = session('2fa:user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $type = session('2fa:type');

        if ($type === 'email') {
            if (
                $user->two_factor_otp !== $request->code ||
                now()->isAfter($user->two_factor_otp_expires_at)
            ) {
                return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
            }
            $user->update([
                'two_factor_otp'           => null,
                'two_factor_otp_expires_at' => null,
            ]);

        } elseif ($type === 'totp') {
            $g2fa  = new Google2FA();
            $valid = $g2fa->verifyKey($user->two_factor_secret, $request->code);
            if (! $valid) {
                return back()->withErrors(['code' => 'Invalid authenticator code. Please try again.']);
            }
        }

        // 2FA passed — complete the login
        session()->forget(['2fa:user_id', '2fa:type', '2fa:remember']);
        Auth::loginUsingId($userId, session('2fa:remember', false));

        session(['2fa:verified' => true]);

        // *** New device login notification ***
        $request = request();
        $lastIp  = $user->last_login_ip;
        $currentIp = $request->ip();

        if ($lastIp !== $currentIp) {
            $user->notify(new NewDeviceLoginNotification(
                $currentIp,
                $request->userAgent()
            ));
        }

        $user->forceFill(['last_login_ip' => $currentIp])->save();

        $user = Auth::user();

        if ($user->isAdmin()) {

            return redirect()->intended(route('dashboard'));
        }
        return redirect()->intended(route('customer.dashboard'));
    }

    // Show the recovery code form
    public function showRecovery()
    {
        if (! session()->has('2fa:user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-recovery');
    }

    // Verify a backup recovery code during login
    public function verifyRecovery(Request $request)
    {
        $request->validate(['recovery_code' => 'required|string']);

        $userId = session('2fa:user_id');
        $user   = User::findOrFail($userId);
        $codes  = $user->two_factor_recovery_codes ?? [];
        $index  = array_search(trim($request->recovery_code), $codes);

        if ($index === false) {
            return back()->withErrors(['recovery_code' => 'Invalid recovery code.']);
        }

        // Remove the used code so it can't be reused
        unset($codes[$index]);
        $user->update(['two_factor_recovery_codes' => array_values($codes)]);

        session()->forget(['2fa:user_id', '2fa:type', '2fa:remember']);
        Auth::loginUsingId($userId);

        $user = Auth::user();

        if ($user->isAdmin()) {

            return redirect()->intended(route('dashboard'));
        }
        return redirect()->intended(route('customer.dashboard'));
    }

    // PROFILE MANAGEMENT
    // Enable Email OTP from profile
    public function enableEmail(Request $request)
    {
        $user = $request->user();
        $user->update([
            'two_factor_type'          => 'email',
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
        ]);

        return back()->with('status', '2fa-enabled');
    }

    // Show TOTP setup page (QR code)
    public function setupTotp(Request $request)
    {
        $g2fa   = new Google2FA();
        $secret = $g2fa->generateSecretKey();

        // Store secret in session until confirmed
        session(['2fa:totp_setup_secret' => $secret]);

        $qrUrl = $g2fa->getQRCodeUrl(
            config('app.name'),
            $request->user()->email,
            $secret
        );

        return view('auth.two-factor-setup-totp', compact('secret', 'qrUrl'));
    }

    // Confirm TOTP setup by verifying a code from the app
    public function confirmTotp(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $secret = session('2fa:totp_setup_secret');
        $g2fa   = new Google2FA();

        if (! $g2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'Code does not match. Please try again.']);
        }

        $request->user()->update([
            'two_factor_type'          => 'totp',
            'two_factor_secret'         => $secret,
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
        ]);

        session()->forget('2fa:totp_setup_secret');

        $request->user()->notify(new TwoFactorEnabledNotification('totp'));

        return redirect()->route('profile.edit')->with('status', '2fa-enabled');
    }

    // Disable 2FA from profile
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        $request->user()->update([
            'two_factor_type'          => null,
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
            'two_factor_otp'            => null,
            'two_factor_otp_expires_at' => null,
        ]);

        $request->user()->notify(new TwoFactorDisabledNotification());

        return back()->with('status', '2fa-disabled');
    }


    // PRIVATE HELPERS
    private function sendEmailOtp(int $userId): void
    {
        $user = User::find($userId);
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'two_factor_otp'           => $code,
            'two_factor_otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user)->send(new TwoFactorOtpMail($code));
    }

    private function generateRecoveryCodes(): array
    {
        return array_map(
            fn() => Str::random(10) . '-' . Str::random(10),
            range(1, 8)
        );
    }
}