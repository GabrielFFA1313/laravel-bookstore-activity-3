<?php

namespace App\Jobs;

use App\Models\Book;
use App\Services\ReviewAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeBookReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public Book $book) {}

    public function handle(ReviewAnalysisService $service): void
    {
        $service->analyzeBookReviews($this->book);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AnalyzeBookReviewsJob failed for book {$this->book->id}: " . $exception->getMessage());
    }
}