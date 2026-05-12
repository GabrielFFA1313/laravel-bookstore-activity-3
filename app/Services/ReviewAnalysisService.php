<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookReviewSummary;
use Illuminate\Support\Facades\Log;

class ReviewAnalysisService
{
    public function __construct(private AIServiceManager $ai) {}

    public function analyzeBookReviews(Book $book): BookReviewSummary
    {
        // 1. Fetch up to 50 recent reviews for this book
        $reviews = $book->reviews()
            ->latest()
            ->take(50)
            ->pluck('comment')
            ->toArray();

        if (empty($reviews)) {
            throw new \Exception("No reviews found for book ID {$book->id}");
        }

        // 2. Build the prompt
        $prompt = $this->buildPrompt($book->title, $reviews);

        // 3. Call AI (with automatic fallback)
        $result = $this->ai->generate($prompt);

        // 4. Parse the structured JSON response
        $parsed = $this->parseAIResponse($result['text']);

        // 5. Save/update the summary
        $summary = BookReviewSummary::updateOrCreate(
            ['book_id' => $book->id],
            [
                'summary'           => $parsed['summary'],
                'sentiment'         => $parsed['sentiment'],
                'sentiment_score'   => $parsed['sentiment_score'],
                'reviews_analyzed'  => count($reviews),
                'ai_provider'       => $result['provider'],
                'last_analyzed_at'  => now(),
            ]
        );

        Log::info("Review analysis complete for book {$book->id} using {$result['provider']}");

        return $summary;
    }

    private function buildPrompt(string $bookTitle, array $reviews): string
    {
        $reviewText = implode("\n---\n", $reviews);

        return <<<PROMPT
You are a book review analyst for PageTurner Online Bookstore.

Analyze the following customer reviews for the book "{$bookTitle}" and respond ONLY with a valid JSON object. No explanation, no markdown, just raw JSON.

Reviews:
{$reviewText}

Respond with this exact JSON structure:
{
  "summary": "A 2-3 sentence summary of what customers commonly say about this book.",
  "sentiment": "positive|neutral|negative",
  "sentiment_score": 0.00
}

Rules:
- "summary" must be 2-3 sentences, written for potential buyers.
- "sentiment" must be exactly one of: positive, neutral, negative.
- "sentiment_score" is a float from 0.00 (very negative) to 1.00 (very positive).
PROMPT;
    }

    private function parseAIResponse(string $raw): array
{
    // Strip any accidental markdown code fences
    $clean = preg_replace('/```json|```/', '', $raw);
    $clean = trim($clean);

    // Fix common Ollama JSON issues
    // Remove trailing periods from numbers e.g. 0.83. -> 0.83
    $clean = preg_replace('/(\d+\.\d+)\.(\s*[}\],])/', '$1$2', $clean);
    // Remove trailing commas before closing braces
    $clean = preg_replace('/,(\s*[}\]])/', '$1', $clean);

    $data = json_decode($clean, true);

    if (!$data || !isset($data['summary'], $data['sentiment'], $data['sentiment_score'])) {
        // Try extracting values manually as last resort
        preg_match('/"summary"\s*:\s*"([^"]+)"/', $clean, $summaryMatch);
        preg_match('/"sentiment"\s*:\s*"([^"]+)"/', $clean, $sentimentMatch);
        preg_match('/"sentiment_score"\s*:\s*([\d.]+)/', $clean, $scoreMatch);

        if ($summaryMatch && $sentimentMatch && $scoreMatch) {
            $data = [
                'summary'         => $summaryMatch[1],
                'sentiment'       => $sentimentMatch[1],
                'sentiment_score' => $scoreMatch[1],
            ];
        } else {
            throw new \Exception('AI returned invalid JSON: ' . $raw);
        }
    }

    // Sanitize sentiment value
    $validSentiments = ['positive', 'neutral', 'negative'];
    if (!in_array($data['sentiment'], $validSentiments)) {
        $data['sentiment'] = 'neutral';
    }

    // Clamp score between 0 and 1
    $data['sentiment_score'] = max(0, min(1, (float) $data['sentiment_score']));

    return $data;
}
}