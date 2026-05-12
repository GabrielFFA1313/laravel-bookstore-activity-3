<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeBookReviewsJob;
use App\Models\Book;
use App\Models\BookReviewSummary;

class ReviewAnalysisController extends Controller
{
    // Admin: trigger analysis for a specific book
    public function analyze(Book $book)
    {
        AnalyzeBookReviewsJob::dispatch($book);

        return response()->json([
            'message' => "Analysis queued for: {$book->title}"
        ]);
    }

    // Public: get the summary for a book
    public function show(Book $book)
    {
        $summary = BookReviewSummary::where('book_id', $book->id)->first();

        if (!$summary) {
            return response()->json(['message' => 'No analysis yet.'], 404);
        }

        return response()->json($summary);
    }

    // Admin: bulk analyze all books (scheduled task)
    public function bulkAnalyze()
    {
        $books = Book::has('reviews')->get();

        foreach ($books as $book) {
            AnalyzeBookReviewsJob::dispatch($book)->delay(now()->addSeconds(2));
        }

        return response()->json(['message' => "{$books->count()} books queued for analysis."]);
    }
}