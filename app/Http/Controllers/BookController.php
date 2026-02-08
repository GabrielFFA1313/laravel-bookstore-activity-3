<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Search by title or author
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $books = $query->paginate(12);
        $categories = Category::all();

        return view('books.index', compact('books', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        // Handle image upload with resizing
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $this->handleImageUpload($request->file('cover_image'));
        }

        Book::create($validated);

        return redirect()->route('books.index')
            ->with('success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
        $book->load(['category', 'reviews.user']);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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

        $book->update($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        // Delete associated images
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
            Storage::disk('public')->delete('thumbnails/' . basename($book->cover_image));
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully!');
    }

    /**
     * Handle image upload with resizing and thumbnail creation
     */
   private function handleImageUpload($file)
{
    // Generate unique filename
    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
    
    // Create image manager with GD driver
    $manager = new ImageManager(new Driver());
    
    // Create main image (max 800x800)
    $image = $manager->read($file);
    $image->scale(width: 800);
    
    // Save main image
    $mainPath = 'covers/' . $filename;
    Storage::disk('public')->put($mainPath, (string) $image->encode());
    
    // Create thumbnail (300x400)
    $thumbnail = $manager->read($file);
    $thumbnail->cover(300, 400);
    
    // Save thumbnail
    $thumbnailPath = 'thumbnails/' . $filename;
    Storage::disk('public')->put($thumbnailPath, (string) $thumbnail->encode());
    
    return $mainPath;
}
}