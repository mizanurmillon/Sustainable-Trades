<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subject;
    public string $message;
    public Order $order;
    protected $type;
    protected $user_id;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $subject, string $type, Order $order, int $user_id)
    {
        $this->message = $message;
        $this->subject = $subject;
        $this->order = $order;
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
            'order' => $this->order->id,
            'user_id' => $this->user_id,
            'order_number' => $this->order->order_number,
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'order',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'order',
            'created_at' => $this->order->created_at,
        ]);
    }
}
