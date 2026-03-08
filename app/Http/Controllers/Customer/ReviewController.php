<?php

namespace App\Http\Controllers\Customer;

use App\Models\Book;
use App\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\NewReviewAdminNotification;
use App\Models\User;

class ReviewController extends Controller
{
    public function store(Request $request, Book $book)
{
    if (!auth()->user()->hasPurchased($book->id)) {
        return back()->with('error', 'You can only review books you have purchased.');
    }

    $validated = $request->validate([
        'rating'  => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:1000',
    ]);

    $validated['user_id'] = auth()->id();
    $validated['book_id'] = $book->id;

    $existingReview = Review::where('user_id', auth()->id())
        ->where('book_id', $book->id)
        ->first();

    if ($existingReview) {
        $existingReview->update($validated);
        $message = 'Review updated successfully!';
        $review  = $existingReview;
    } else {
        $review  = Review::create($validated);
        $message = 'Review submitted successfully!';
    }

    // *** Notify all admins ***
    $review->load(['book', 'user']); // make sure relations are loaded
    User::where('role', 'admin')->get()
        ->each(fn($admin) => $admin->notify(new NewReviewAdminNotification($review)));

    return redirect()->route('books.show', $book)
        ->with('success', $message);
}

    public function destroy(Review $review)
    {
        // Only allow owner or admin to delete
        if (auth()->id() !== $review->user_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'Review deleted successfully!');
    }
}