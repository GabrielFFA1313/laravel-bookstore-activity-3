<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorDisabledNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Disabled')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Two-factor authentication has been disabled on your account.')
            ->line('If you did not make this change, please contact support immediately.')
            ->action('Go to Profile', route('profile.edit'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '2FA Disabled',
            'message' => 'Two-factor authentication has been disabled on your account.',
            'icon'    => '🔓',
        ];
    }
}