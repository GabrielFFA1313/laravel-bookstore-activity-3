<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BooksExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;
    protected array $columns;

    public function __construct(array $filters = [], array $columns = [])
    {
        $this->filters = $filters;
        $this->columns = $columns ?: ['isbn', 'title', 'author', 'price', 'stock', 'category', 'description', 'created_at'];
    }

    public function query()
    {
        $query = Book::query()->with('category');

        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (!empty($this->filters['price_min'])) {
            $query->where('price', '>=', $this->filters['price_min']);
        }

        if (!empty($this->filters['price_max'])) {
            $query->where('price', '<=', $this->filters['price_max']);
        }

        if (!empty($this->filters['stock_status'])) {
            if ($this->filters['stock_status'] === 'in_stock') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($this->filters['stock_status'] === 'out_of_stock') {
                $query->where('stock_quantity', 0);
            }
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    public function headings(): array
    {
        $allHeadings = [
            'isbn'        => 'ISBN',
            'title'       => 'Title',
            'author'      => 'Author',
            'price'       => 'Price',
            'stock'       => 'Stock',
            'category'    => 'Category',
            'description' => 'Description',
            'created_at'  => 'Created At',
        ];

        return array_values(array_intersect_key($allHeadings, array_flip($this->columns)));
    }

    public function map($book): array
    {
        $allFields = [
            'isbn'        => $book->isbn,
            'title'       => $book->title,
            'author'      => $book->author,
            'price'       => $book->price,
            'stock'       => $book->stock_quantity,
            'category'    => $book->category?->name,
            'description' => $book->description,
            'created_at'  => $book->created_at?->format('Y-m-d'),
        ];

        return array_values(array_intersect_key($allFields, array_flip($this->columns)));
    }
}