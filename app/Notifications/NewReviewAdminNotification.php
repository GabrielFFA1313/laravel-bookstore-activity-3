<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public Review $review)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Review Submitted - ' . $this->review->book->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new review has been submitted on your store.')
            ->line('**Book:** ' . $this->review->book->title)
            ->line('**Customer:** ' . $this->review->user->name)
            ->line('**Rating:** ' . $this->review->rating . '/5')
            ->line('**Comment:** ' . ($this->review->comment ?? 'No comment left.'))
            ->action('View Book', route('books.show', $this->review->book))
            ->line('Log in to your admin panel to manage this review.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'New Review Submitted',
            'message' => $this->review->user->name . ' reviewed "' . $this->review->book->title . '" - ' . $this->review->rating . '/5 stars',
            'book_id' => $this->review->book_id,
        ];
    }
}