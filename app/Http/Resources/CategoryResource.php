<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            // Only include book_count if it was loaded via withCount()
            // Prevents accidental extra query if forgotten
            'book_count' => $this->whenAppended('books_count'),
        ];
    }
}