<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DailySalesReportNotification extends Notification
{
    use Queueable;

    protected array $stats;
    protected string $date;

    public function __construct(array $stats, string $date)
    {
        $this->stats = $stats;
        $this->date  = $date;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Daily Sales Report — {$this->date}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Here is the sales summary for **{$this->date}**:")
            ->line("**Total Orders:** {$this->stats['total_orders']}")
            ->line("**Revenue:** \${$this->stats['revenue']}")
            ->line("**New Customers:** {$this->stats['new_customers']}")
            ->line("**Pending Orders:** {$this->stats['pending_orders']}")
            ->action('View Full Report', url('/dashboard'))
            ->line('This report is automatically generated daily.');
    }
}