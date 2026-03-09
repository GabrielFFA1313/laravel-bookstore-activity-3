<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Counts ──────────────────────────────────────────
        $totalUsers      = User::where('role', 'customer')->count();
        $totalBooks      = Book::count();
        $totalCategories = Category::count();
        $totalOrders     = Order::count();

        // ── Order status summary ─────────────────────────────
        $orderStatuses = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present even if count is 0
        $allStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        foreach ($allStatuses as $status) {
            $orderStatuses[$status] = $orderStatuses[$status] ?? 0;
        }

        // ── Recent orders ────────────────────────────────────
        $recentOrders = Order::with(['user', 'orderItems.book'])
            ->latest()
            ->take(8)
            ->get();

        // ── Recent reviews ───────────────────────────────────
        $recentReviews = Review::with(['user', 'book'])
            ->latest()
            ->take(5)
            ->get();

        // ── Revenue ──────────────────────────────────────────
        $totalRevenue = Order::whereNotIn('status', ['cancelled'])->sum('total_amount');

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalBooks',
            'totalCategories',
            'totalOrders',
            'orderStatuses',
            'recentOrders',
            'recentReviews',
            'totalRevenue',
        ));
    }
}