<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Has Been Reset')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your password has been successfully changed.')
            ->line('If you did not make this change, please contact support immediately.')
            ->action('Login', route('login'))
            ->line('If this was you, no further action is needed.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Password Reset',
            'message' => 'Your password has been successfully changed.',
            'icon'    => '🔑',
        ];
    }
}