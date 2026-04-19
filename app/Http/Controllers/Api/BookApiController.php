<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class BookApiController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Book::with('category')
            ->when($request->search, fn($q) =>
                $q->where('title', 'ilike', '%' . $request->search . '%')
                  ->orWhere('author', 'ilike', '%' . $request->search . '%')
            )
            ->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id)
            )
            ->when($request->min_price, fn($q) =>
                $q->where('price', '>=', $request->min_price)
            )
            ->when($request->max_price, fn($q) =>
                $q->where('price', '<=', $request->max_price)
            )
            ->orderBy('id');

        // Cursor-based pagination
        $books = $query->cursorPaginate(20);

        return response()->json([
            'data'       => $books->items(),
            'next_cursor' => $books->nextCursor()?->encode(),
            'prev_cursor' => $books->previousCursor()?->encode(),
            'per_page'   => $books->perPage(),
            'has_more'   => $books->hasMorePages(),
        ]);
    }

    public function show(Book $book)
    {
        return response()->json([
            'data' => $book->load('category'),
        ]);
    }

    public function adminIndex(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $books = Book::with('category')
            ->latest()
            ->cursorPaginate(50);

        return response()->json([
            'data'        => $books->items(),
            'next_cursor' => $books->nextCursor()?->encode(),
            'per_page'    => $books->perPage(),
            'has_more'    => $books->hasMorePages(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Book::class);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'isbn'           => 'required|string|unique:books',
            'price'          => 'required|numeric|min:0|max:9999.99',
            'stock_quantity' => 'required|integer|min:0',
            'category_id'    => 'required|exists:categories,id',
            'description'    => 'nullable|string',
        ]);

        $book = Book::create($validated);

        return response()->json(['data' => $book], 201);
    }

    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'author'         => 'sometimes|string|max:255',
            'price'          => 'sometimes|numeric|min:0|max:9999.99',
            'stock_quantity' => 'sometimes|integer|min:0',
            'category_id'    => 'sometimes|exists:categories,id',
            'description'    => 'nullable|string',
        ]);

        $book->update($validated);

        return response()->json(['data' => $book]);
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        $book->delete();

        return response()->json(['message' => 'Book deleted successfully.']);
    }
}