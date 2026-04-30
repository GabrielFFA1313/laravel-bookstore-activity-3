<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ── Core fields — always included ──
            'id'             => $this->id,
            'isbn'           => $this->isbn,
            'title'          => $this->title,
            'author'         => $this->author,
            'publisher'      => $this->publisher,
            'format'         => $this->format,
            'price'          => number_format((float) $this->price, 2),
            'stock_quantity' => $this->stock_quantity,
            'is_active'      => $this->is_active,
            'published_at'   => $this->published_at,

            // ── Conditional field — only on books.show route ──
            // Avoids sending large description text in list views
            'description' => $this->when(
                $request->routeIs('books.show'),
                $this->description
            ),

            // ── Relationship — safe with whenLoaded() ──
            // Returns null instead of triggering a query if not eager-loaded
            'category' => new CategoryResource($this->whenLoaded('category')),

            // ── Timestamps ──
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}