<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;                  
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\Shardable;

class Book extends Model implements Auditable
{
    use HasFactory, AuditableTrait, Searchable, Shardable;  

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
        // Lab 7
        'format',
        'is_active',
        'publisher',
        'published_at',
    ];

    protected $casts = [
        'published_at'   => 'date',
        'is_active'      => 'boolean',
        'price'          => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    // ─────────────────────────────────────────────
    // SCOUT — Controls which fields are indexed
    // ─────────────────────────────────────────────

    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'author'      => $this->author,
            'publisher'   => $this->publisher,
            'description' => $this->description,
            'format'      => $this->format,
        ];
    }

    /**
     * Only index active books — inactive books are
     * removed from the search index automatically.
     */
    public function shouldBeSearchable(): bool
{
    return (bool) $this->is_active;
}

/**
 * Modify the query used by Scout to filter inactive books
 * from search results at the query level.
 */
public function makeAllSearchableUsing($query)
{
    return $query->where('is_active', true);
}

    // ─────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────

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

    // ─────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getThumbnailAttribute()
    {
        if ($this->cover_image) {
            return 'thumbnails/' . basename($this->cover_image);
        }
        return null;
    }

    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/placeholder-book.png');
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/placeholder-book.png');
    }
}