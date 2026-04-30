<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BookRepository
{
    private const CACHE_TTL_SHORT  = 300;
    private const CACHE_TTL_MEDIUM = 1800;
    private const CACHE_TTL_LONG   = 86400;

    // ─────────────────────────────────────────────
    // 1. CATALOG LISTING — Cursor Pagination
    // ─────────────────────────────────────────────
    public function getActiveCatalog(int $perPage = 100): mixed
    {
        return Cache::tags(['books', 'catalog'])
            ->remember('catalog:active', self::CACHE_TTL_SHORT, function () use ($perPage) {
                return Book::select([
                        'books.id', 'books.isbn', 'books.title', 'books.author',
                        'books.price', 'books.stock_quantity',
                        'books.published_at', 'books.category_id',
                    ])
                    ->with(['category:id,name'])
                    ->where('is_active', true)
                    ->orderBy('published_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->cursorPaginate($perPage);
            });
    }

    // ─────────────────────────────────────────────
    // 2. ISBN LOOKUP
    // ─────────────────────────────────────────────
    public function findByIsbn(string $isbn): ?Book
    {
        return Cache::tags(['books', 'isbn'])
            ->remember("isbn:{$isbn}", self::CACHE_TTL_LONG, function () use ($isbn) {
                return Book::select([
                        'id', 'isbn', 'title', 'author', 'publisher',
                        'price', 'stock_quantity', 'format', 'is_active', 'category_id',
                    ])
                    ->where('isbn', $isbn)
                    ->first();
            });
    }

    // ─────────────────────────────────────────────
    // 3. CATEGORY FILTER
    // ─────────────────────────────────────────────
    public function getByCategory(int $categoryId, int $perPage = 100): mixed
    {
        return Cache::tags(['books', "category:{$categoryId}"])
            ->remember("category:{$categoryId}:page", self::CACHE_TTL_MEDIUM, function () use ($categoryId, $perPage) {
                return Book::select([
                        'id', 'isbn', 'title', 'author',
                        'price', 'stock_quantity', 'category_id', 'published_at',
                    ])
                    ->where('category_id', $categoryId)
                    ->where('is_active', true)
                    ->orderBy('published_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->cursorPaginate($perPage);
            });
    }

    // ─────────────────────────────────────────────
    // 4. FULL-TEXT SEARCH
    // ─────────────────────────────────────────────
    public function searchFullText(string $query, int $perPage = 100): mixed
    {
        return Cache::tags(['books', 'search'])
            ->remember('search:' . md5($query), self::CACHE_TTL_SHORT, function () use ($query, $perPage) {
                return Book::search($query)
                    ->query(fn($q) => $q->where('is_active', true))
                    ->paginate($perPage);
            });
    }

    // ─────────────────────────────────────────────
    // 5. EXPORT — Routed to READ replica
    //    Heavy streaming query offloaded from write master
    // ─────────────────────────────────────────────
    public function streamForExport(callable $callback, int $chunkSize = 10000): void
    {
        // Use read replica for heavy export queries
        // Prevents export load from affecting write performance
        Book::on('pgsql::read')
            ->select([
                'id', 'isbn', 'title', 'author', 'publisher',
                'price', 'stock_quantity', 'format',
                'is_active', 'category_id', 'published_at',
            ])
            ->where('is_active', true)
            ->orderBy('id')
            ->lazyById($chunkSize)
            ->each($callback);
    }

    // ─────────────────────────────────────────────
    // 6. PRICE RANGE
    // ─────────────────────────────────────────────
    public function getByPriceRange(float $minPrice, float $maxPrice, int $perPage = 100): mixed
    {
        return Cache::tags(['books', 'price'])
            ->remember("price:{$minPrice}:{$maxPrice}", self::CACHE_TTL_MEDIUM, function () use ($minPrice, $maxPrice, $perPage) {
                return Book::select([
                        'id', 'isbn', 'title', 'price', 'stock_quantity',
                    ])
                    ->whereBetween('price', [$minPrice, $maxPrice])
                    ->where('is_active', true)
                    ->orderBy('price')
                    ->orderBy('id')
                    ->cursorPaginate($perPage);
            });
    }

    // ─────────────────────────────────────────────
    // 7. REPORTING — Explicitly uses read replica
    //    Materialized view queries for dashboards
    // ─────────────────────────────────────────────
    public function getBestsellerStats(): \Illuminate\Support\Collection
    {
        // Route to read replica — no need to hit write master
        return DB::connection('pgsql::read')
            ->table('mv_bestseller_stats')
            ->join('categories', 'mv_bestseller_stats.category_id', '=', 'categories.id')
            ->select('categories.name', 'mv_bestseller_stats.*')
            ->get();
    }

    public function getInventorySummary(): \Illuminate\Support\Collection
    {
        return DB::connection('pgsql::read')
            ->table('mv_inventory_summary')
            ->join('categories', 'mv_inventory_summary.category_id', '=', 'categories.id')
            ->select('categories.name', 'mv_inventory_summary.*')
            ->get();
    }

    // ─────────────────────────────────────────────
    // CACHE INVALIDATION
    // ─────────────────────────────────────────────
    public function invalidateBookCache(string $isbn, int $categoryId): void
    {
        Cache::tags(['books'])->flush();
        Cache::tags(["category:{$categoryId}"])->flush();
    }
}