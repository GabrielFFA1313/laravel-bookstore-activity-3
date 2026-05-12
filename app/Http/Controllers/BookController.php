<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $query = Book::with('category')
                ->withAvg('reviews', 'rating')
                ->withCount('reviews');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('author', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('min_rating')) {
            $minRating = $request->min_rating;
            $bookIds = \DB::table('reviews')
                ->select('book_id')
                ->groupBy('book_id')
                ->havingRaw('AVG(rating) >= ?', [$minRating])
                ->pluck('book_id');
            $query->whereIn('id', $bookIds);
        }
        // Format filter
            if ($request->filled('format')) {
                $query->where('format', $request->format);
            }

            // Publisher filter
            if ($request->filled('publisher')) {
                $query->where('publisher', 'ilike', "%{$request->publisher}%");
            }

            // Published year filter
            if ($request->filled('published_year')) {
                $query->whereRaw('EXTRACT(YEAR FROM published_at) = ?', [(int) $request->published_year]);
            }

            // Hide inactive books from non-admins
            if (!auth()->check() || !auth()->user()->isAdmin()) {
                $query->where('is_active', true);
            }

        $sortBy = $request->get('sort', 'title');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->leftJoin(\DB::raw('(SELECT book_id, AVG(rating) as avg_rating FROM reviews GROUP BY book_id) as review_avg'),
                    'books.id', '=', 'review_avg.book_id')
                    ->orderByRaw('COALESCE(review_avg.avg_rating, 0) DESC')
                    ->select('books.*');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('title', 'asc');
                break;
        }

        $books = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('books.index', compact('books', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', Book::class);

        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Book::class);

        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'isbn'           => 'required|string|unique:books',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description'    => 'nullable|string',
            'cover_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'publisher'      => 'nullable|string|max:255',
            'published_at'   => 'nullable|date',
            'format'         => 'nullable|in:hardcover,paperback,ebook,audiobook',
            'is_active'      => 'boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $this->handleImageUpload($request->file('cover_image'));
        }

        $validated['is_active'] = $request->boolean('is_active');
        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
    $book->load(['category', 'reviews.user']);
    $book->loadAvg('reviews', 'rating');
    $book->loadCount('reviews');
    return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'isbn'           => 'required|string|unique:books,isbn,' . $book->id,
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description'    => 'nullable|string',
            'cover_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'publisher'      => 'nullable|string|max:255',
            'published_at'   => 'nullable|date',
            'format'         => 'nullable|in:hardcover,paperback,ebook,audiobook',
            'is_active'      => 'boolean',
        ]);

            // Handle image upload
        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
                Storage::disk('public')->delete('thumbnails/' . basename($book->cover_image));
            }
            $validated['cover_image'] = $this->handleImageUpload($request->file('cover_image'));
        }

        $validated['is_active'] = $request->boolean('is_active');
        $book->update($validated);

        return redirect()->route('books.show', $book)->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
            Storage::disk('public')->delete('thumbnails/' . basename($book->cover_image));
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Book deleted successfully!');
    }

    private function handleImageUpload($file)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $manager  = new ImageManager(new Driver());

        $image = $manager->read($file);
        $image->scale(width: 800);
        $mainPath = 'covers/' . $filename;
        Storage::disk('public')->put($mainPath, (string) $image->encode());

        $thumbnail = $manager->read($file);
        $thumbnail->cover(300, 400);
        $thumbnailPath = 'thumbnails/' . $filename;
        Storage::disk('public')->put($thumbnailPath, (string) $thumbnail->encode());

        return $mainPath;
    }
}