<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookReviewSummary extends Model
{
    protected $fillable = [
        'book_id', 'summary', 'sentiment',
        'sentiment_score', 'reviews_analyzed',
        'ai_provider', 'last_analyzed_at',
    ];

    protected $casts = [
        'last_analyzed_at' => 'datetime',
        'sentiment_score'  => 'float',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Helper: returns a color for the UI badge
    public function getSentimentColorAttribute(): string
    {
        return match($this->sentiment) {
            'positive' => 'green',
            'negative' => 'red',
            default    => 'yellow',
        };
    }
}