<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class QueryCacheService
{
    // Cache TTL constants
    const TTL_CATEGORIES   = 3600;   // 1 hour — categories rarely change
    const TTL_BESTSELLERS  = 1800;   // 30 min — bestseller list
    const TTL_BOOK_COUNT   = 300;    // 5 min — book counts
    const TTL_STATS        = 600;    // 10 min — dashboard stats

    // ── Categories ───────────────────────────────────────────────────────

    public static function allCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('categories:all', self::TTL_CATEGORIES, function () {
            return Category::orderBy('name')->get();
        });
    }

    public static function categoryWithBooks(int $categoryId): ?Category
    {
        return Cache::remember("categories:with_books:{$categoryId}", self::TTL_CATEGORIES, function () use ($categoryId) {
            return Category::with('books')->find($categoryId);
        });
    }

    public static function forgetCategories(): void
    {
        Cache::forget('categories:all');
    }

    // ── Books ─────────────────────────────────────────────────────────────

    public static function bestsellers(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("books:bestsellers:{$limit}", self::TTL_BESTSELLERS, function () use ($limit) {
            return Book::with('category')
                ->withCount(['orderItems as total_sold' => function ($q) {
                    $q->whereHas('order', fn($o) => $o->where('status', '!=', 'cancelled'));
                }])
                ->orderByDesc('total_sold')
                ->limit($limit)
                ->get();
        });
    }

    public static function featuredBooks(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("books:featured:{$limit}", self::TTL_BESTSELLERS, function () use ($limit) {
            return Book::with('category')
                ->where('stock_quantity', '>', 0)
                ->latest()
                ->limit($limit)
                ->get();
        });
    }

    public static function bookCount(): int
    {
        return Cache::remember('books:count', self::TTL_BOOK_COUNT, function () {
            return Book::count();
        });
    }

    public static function forgetBooks(): void
    {
        Cache::forget('books:bestsellers:10');
        Cache::forget('books:featured:8');
        Cache::forget('books:count');
    }

    // ── Dashboard Stats ───────────────────────────────────────────────────

    public static function adminStats(): array
    {
        return Cache::remember('stats:admin', self::TTL_STATS, function () {
            return [
                'total_customers' => \App\Models\User::where('role', 'customer')->count(),
                'total_books'     => Book::count(),
                'total_orders'    => Order::count(),
                'total_revenue'   => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
                'pending_orders'  => Order::where('status', 'pending')->count(),
                'month_revenue'   => Order::where('status', '!=', 'cancelled')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount'),
            ];
        });
    }

    public static function forgetStats(): void
    {
        Cache::forget('stats:admin');
    }

    // ── Invalidate all caches (call after bulk imports) ───────────────────

    public static function flushAll(): void
    {
        self::forgetCategories();
        self::forgetBooks();
        self::forgetStats();
    }
}