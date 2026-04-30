<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Repositories\BookRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    public function __construct(
        protected BookRepository $repository
    ) {}

    // ─────────────────────────────────────────────
    // INDEX — Catalog listing
    // GET /api/books
    // No N+1: category eager-loaded with select
    // ─────────────────────────────────────────────

    public function index(Request $request): AnonymousResourceCollection
    {
        $books = Book::select([
                'id', 'isbn', 'title', 'author', 'publisher',
                'price', 'stock_quantity', 'format',
                'is_active', 'category_id', 'published_at',
            ])
            ->with(['category:id,name'])   // Eager load — prevents N+1
            ->where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate(100);

        return BookResource::collection($books);
    }

    // ─────────────────────────────────────────────
    // SHOW — Single book detail
    // GET /api/books/{book}
    // Loads description only here (not in list)
    // ─────────────────────────────────────────────

    public function show(Book $book): BookResource
    {
        // Load full details including description and category
        $book->load('category:id,name');

        return new BookResource($book);
    }

    // ─────────────────────────────────────────────
    // SEARCH — Full-text search
    // GET /api/books/search?q=laravel
    // ─────────────────────────────────────────────

    public function search(Request $request): AnonymousResourceCollection
    {
        $query = $request->string('q')->trim()->toString();

        $books = Book::select([
                'id', 'isbn', 'title', 'author',
                'price', 'stock_quantity', 'category_id',
            ])
            ->with(['category:id,name'])
            ->whereFullText(['title', 'description'], $query)
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->cursorPaginate(100);

        return BookResource::collection($books);
    }

    // ─────────────────────────────────────────────
    // BY CATEGORY — Filter by category
    // GET /api/books/category/{id}
    // ─────────────────────────────────────────────

    public function byCategory(Request $request, int $categoryId): AnonymousResourceCollection
    {
        $books = $this->repository->getByCategory($categoryId);

        // Manually load category to prevent N+1
        // (repository returns plain paginator without relations)
        $books->getCollection()->load('category:id,name');

        return BookResource::collection($books);
    }
}