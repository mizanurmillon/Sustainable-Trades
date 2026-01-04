<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subject;
    public string $message;
    public Review $review;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $subject, string $type, Review $review)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->type = $type;
        $this->review = $review;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'review' => $this->review->id,
            'rating' => $this->review->rating,
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'review',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'review',
            'created_at' => $this->review->created_at,
        ]);
    }
}
