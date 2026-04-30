<?php

namespace App\Observers;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;

class BookObserver
{
    /**
     * Fires when a book is created or updated.
     * Invalidates all related caches to prevent stale data.
     */
    public function saved(Book $book): void
    {
        // Flush entire books catalog cache
        Cache::tags(['books', 'catalog'])->flush();

        // Flush specific ISBN cache
        Cache::forget("isbn:{$book->isbn}");

        // Flush category-specific cache
        Cache::tags(["category:{$book->category_id}"])->flush();

        // Flush search cache — search results may now be stale
        Cache::tags(['books', 'search'])->flush();

        // If category changed, also flush old category cache
        if ($book->wasChanged('category_id') && $book->getOriginal('category_id')) {
            Cache::tags(["category:{$book->getOriginal('category_id')}"])->flush();
        }
    }

    /**
     * Fires when a book is deleted.
     */
    public function deleted(Book $book): void
    {
        Cache::tags(['books', 'catalog'])->flush();
        Cache::forget("isbn:{$book->isbn}");
        Cache::tags(["category:{$book->category_id}"])->flush();
        Cache::tags(['books', 'search'])->flush();
    }

    /**
     * Fires when a book is created (before saved).
     */
    public function created(Book $book): void
    {
        // Trigger cache warmup for this category in the background
        \App\Jobs\WarmCategoryCache::dispatch($book->category_id);
    }
}