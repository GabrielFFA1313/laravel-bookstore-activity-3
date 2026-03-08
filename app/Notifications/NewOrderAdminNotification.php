<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Order Received - #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new order has been placed on your store.')
            ->line('**Order #:** ' . $this->order->id)
            ->line('**Customer:** ' . $this->order->user->name)
            ->line('**Total:** $' . number_format($this->order->total_amount, 2))
            ->line('**Status:** ' . ucfirst($this->order->status))
            ->action('View Order', route('orders.show', $this->order))
            ->line('Login to your admin panel to manage this order.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'    => 'New Order Received',
            'message'  => 'Order #' . $this->order->id . ' placed by ' . $this->order->user->name . ' — $' . number_format($this->order->total_amount, 2),
            'icon'     => '🛒',
            'order_id' => $this->order->id,
        ];
    }
}