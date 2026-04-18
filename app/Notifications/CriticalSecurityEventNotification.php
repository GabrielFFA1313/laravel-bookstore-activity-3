<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CriticalSecurityEventNotification extends Notification
{
    use Queueable;

    protected string $event;
    protected array  $details;
    protected string $performedBy;
    protected string $ipAddress;

    public function __construct(string $event, array $details, string $performedBy, string $ipAddress)
    {
        $this->event       = $event;
        $this->details     = $details;
        $this->performedBy = $performedBy;
        $this->ipAddress   = $ipAddress;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('⚠️ Security Alert — ' . ucfirst(str_replace('_', ' ', $this->event)))
            ->greeting('Security Alert!')
            ->line('A critical security event was detected on PageTurner.')
            ->line('**Event:** ' . ucfirst(str_replace('_', ' ', $this->event)))
            ->line('**Performed by:** ' . $this->performedBy)
            ->line('**IP Address:** ' . $this->ipAddress)
            ->line('**Time:** ' . now()->format('F d, Y g:i:s A'));

        foreach ($this->details as $key => $value) {
            $mail->line('**' . ucfirst(str_replace('_', ' ', $key)) . ':** ' . $value);
        }

        return $mail
            ->action('View Audit Logs', url('/admin/audit'))
            ->line('If this was not an authorized action, please investigate immediately.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event'        => $this->event,
            'details'      => $this->details,
            'performed_by' => $this->performedBy,
            'ip_address'   => $this->ipAddress,
            'icon'         => 'shield',
        ];
    }
}