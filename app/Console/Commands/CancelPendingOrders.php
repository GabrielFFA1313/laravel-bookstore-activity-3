<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelPendingOrders extends Command
{
    protected $signature   = 'order:cleanup-pending';
    protected $description = 'Cancel pending orders that are older than 24 hours';

    public function handle(): void
    {
        $orders = Order::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $item->book->increment('stock_quantity', $item->quantity);
            }
            $order->update(['status' => 'cancelled']);
            $order->user->notify(new OrderStatusUpdatedNotification($order));
            $count++;
        }

        Log::info("order:cleanup-pending — cancelled {$count} pending order(s).");
        $this->info("Cancelled {$count} pending order(s).");
    }
}