<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Book extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected array $auditExclude = ['cover_image'];

    protected $fillable = [
        'category_id',
        'title',
        'author',
        'isbn',
        'price',
        'stock_quantity',
        'description',
        'cover_image',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessor for average rating
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    // Get thumbnail path
    public function getThumbnailAttribute()
    {
        if ($this->cover_image) {
            return 'thumbnails/' . basename($this->cover_image);
        }
        return null;
    }

    // Get cover image URL with fallback to placeholder
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/placeholder-book.png');
    }

    // Get thumbnail URL with fallback
    public function getThumbnailUrlAttribute()
    {
        if ($this->cover_image) {
            // If using the simple upload (no thumbnails), use main image
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/placeholder-book.png');
    }
}