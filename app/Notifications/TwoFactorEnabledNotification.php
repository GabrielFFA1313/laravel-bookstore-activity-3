<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorEnabledNotification extends Notification
{
    use Queueable;

    public function __construct(public string $type)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $method = $this->type === 'totp' ? 'Authenticator App' : 'Email OTP';

        return (new MailMessage)
            ->subject('Two-Factor Authentication Enabled')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Two-factor authentication has been enabled on your account via **{$method}**.")
            ->line('Your account is now more secure.')
            ->line('If you did not make this change, please contact support immediately.')
            ->action('Go to Profile', route('profile.edit'));
    }

    public function toArray(object $notifiable): array
    {
        $method = $this->type === 'totp' ? 'Authenticator App' : 'Email OTP';

        return [
            'title'   => '2FA Enabled',
            'message' => "Two-factor authentication ({$method}) has been enabled on your account.",
            'icon'    => '🔒',
        ];
    }
}