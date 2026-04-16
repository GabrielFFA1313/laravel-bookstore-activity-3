<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use App\Notifications\DailySalesReportNotification;
use Illuminate\Console\Command;

class SendDailySalesReport extends Command
{
    protected $signature   = 'reports:daily-sales';
    protected $description = 'Send daily sales report email to all admins';

    public function handle(): void
    {
        $date = now()->subDay()->format('F d, Y');

        $stats = [
            'total_orders'   => Order::whereDate('created_at', now()->subDay())->count(),
            'revenue'        => number_format(
                Order::whereDate('created_at', now()->subDay())
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount'), 2
            ),
            'new_customers'  => User::whereDate('created_at', now()->subDay())
                ->where('role', 'customer')
                ->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new DailySalesReportNotification($stats, $date));
        }

        $this->info("Daily sales report sent to {$admins->count()} admin(s).");
    }
}