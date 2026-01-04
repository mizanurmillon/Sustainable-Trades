<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProductNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subject;
    public string $message;
    public Product $product;
    protected $type;

    protected $user_id;



    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $subject, string $type, Product $product, int $user_id)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->product = $product;
        $this->type = $type;
        $this->user_id = $user_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
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
            'product' => $this->product->id,
            'user_id' => $this->product->user_id,
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'product',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'product',
            'created_at' => $this->product->created_at,
        ]);
    }
}
