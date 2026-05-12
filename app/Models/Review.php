<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\AnalyzeBookReviewsJob;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'rating', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    protected static function booted(): void
{
    static::created(function ($review) {
        $book = \App\Models\Book::find($review->book_id);
        if ($book) {
            \App\Jobs\AnalyzeBookReviewsJob::dispatch($book)
                ->delay(now()->addSeconds(5));
        }
    });
}
}