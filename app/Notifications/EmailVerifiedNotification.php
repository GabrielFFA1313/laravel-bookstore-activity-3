<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerifiedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Email Verified Successfully')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your email address has been successfully verified.')
            ->line('You now have full access to all features.')
            ->action('Go to Homepage', url('/'))
            ->line('Thank you for verifying your email!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Email Verified',
            'message' => 'Your email address has been successfully verified.',
            'icon'    => '✅',
        ];
    }
}