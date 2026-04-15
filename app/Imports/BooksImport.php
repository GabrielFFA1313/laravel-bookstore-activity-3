<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class BooksImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnFailure
{
    use SkipsFailures;

    protected string $duplicateAction; // 'skip' or 'update'

    public function __construct(string $duplicateAction = 'skip')
    {
        $this->duplicateAction = $duplicateAction;
    }

    public function model(array $row): ?Book
    {
        // Resolve category
        $category = Category::whereRaw('LOWER(name) = ?', [strtolower(trim($row['category']))])->first();

        if (!$category) {
            return null;
        }

        // Handle duplicates by ISBN
        $existing = Book::where('isbn', trim($row['isbn']))->first();

        if ($existing) {
            if ($this->duplicateAction === 'update') {
                $existing->update([
                    'title'          => trim($row['title']),
                    'author'         => trim($row['author']),
                    'price'          => $row['price'],
                    'stock_quantity' => $row['stock'],
                    'category_id'    => $category->id,
                    'description'    => $row['description'] ?? null,
                ]);
            }
            // skip: do nothing
            return null;
        }

        return new Book([
            'isbn'           => trim($row['isbn']),
            'title'          => trim($row['title']),
            'author'         => trim($row['author']),
            'price'          => $row['price'],
            'stock_quantity' => $row['stock'],
            'category_id'    => $category->id,
            'description'    => $row['description'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'isbn'   => ['required', 'string'],
            'title'  => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'price'  => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'stock'  => ['required', 'integer', 'min:0'],
            'category' => ['required', 'string'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'price.max'    => 'Price cannot exceed 9999.99.',
            'stock.min'    => 'Stock cannot be negative.',
            'category.required' => 'Category is required.',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}