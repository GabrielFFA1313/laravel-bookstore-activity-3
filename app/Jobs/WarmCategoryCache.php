<?php

namespace App\Jobs;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class WarmCategoryCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly int $categoryId
    ) {}

    public function handle(): void
    {
        // Pre-load top 1,000 books per category into Redis
        // Ensures cache hits from the very first user request
        $books = Book::select([
                'id', 'title', 'author', 'price',
                'stock_quantity', 'isbn', 'format',
            ])
            ->where('category_id', $this->categoryId)
            ->where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(1000)
            ->get();

        // Store for 2 hours — tagged for easy invalidation
        Cache::tags(["category:{$this->categoryId}"])
            ->put(
                "category:{$this->categoryId}:popular",
                $books,
                7200
            );
    }

    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error(
            "WarmCategoryCache failed for category {$this->categoryId}: " .
            $exception->getMessage()
        );
    }
}