<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
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
        $statusMessages = [
            'processing' => 'Your order is now being processed.',
            'shipped'    => 'Great news! Your order has been shipped.',
            'delivered'  => 'Your order has been delivered. Enjoy!',
            'cancelled'  => 'Your order has been cancelled.',
        ];

        $message = $statusMessages[$this->order->status] ?? 'Your order status has been updated.';

        return (new MailMessage)
            ->subject('Order #' . $this->order->id . ' Status Updated')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($message)
            ->line('**Order #:** ' . $this->order->id)
            ->line('**New Status:** ' . ucfirst($this->order->status))
            ->line('**Total:** $' . number_format($this->order->total_amount, 2))
            ->action('View Order', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'    => 'Order #' . $this->order->id . ' Updated',
            'message'  => 'Your order status changed to: ' . ucfirst($this->order->status),
            'icon'     => '📦',
            'order_id' => $this->order->id,
        ];
    }
}