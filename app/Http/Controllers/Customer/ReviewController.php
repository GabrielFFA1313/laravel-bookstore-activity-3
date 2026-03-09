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
        $this->authorize('create', [Review::class, $book]);

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

        $review->load(['book', 'user']);
        User::where('role', 'admin')->get()
            ->each(fn($admin) => $admin->notify(new NewReviewAdminNotification($review)));

        return redirect()->route('books.show', $book)->with('success', $message);
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)->with('success', 'Review deleted successfully!');
    }
}