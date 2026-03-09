<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Order summary
        $totalOrders  = $user->orders()->count();
        $recentOrders = $user->orders()
            ->with('orderItems.book')
            ->latest()
            ->take(5)
            ->get();

        // Order status counts
        $orderStatuses = $user->orders()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $allStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        foreach ($allStatuses as $status) {
            $orderStatuses[$status] = $orderStatuses[$status] ?? 0;
        }

        // Recently purchased books (from delivered orders)
        $recentBooks = $user->orders()
            ->with('orderItems.book.category')
            ->whereIn('status', ['delivered', 'shipped', 'processing'])
            ->latest()
            ->take(3)
            ->get()
            ->pluck('orderItems')
            ->flatten()
            ->pluck('book')
            ->unique('id')
            ->take(6);

        // Review activity
        $recentReviews = $user->reviews()
            ->with('book')
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', compact(
            'user',
            'totalOrders',
            'recentOrders',
            'orderStatuses',
            'recentBooks',
            'recentReviews',
        ));
    }
}