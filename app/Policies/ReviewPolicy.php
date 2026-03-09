<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Only verified purchasers can create a review.
     */
    public function create(User $user, Book $book): bool
    {
        return $user->hasVerifiedEmail() && $user->hasPurchased($book->id);
    }

    /**
     * Only the review owner or admin can delete a review.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin() || $user->id === $review->user_id;
    }
}