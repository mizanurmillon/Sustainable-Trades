<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageNotification extends Notification
{
    use Queueable;

    protected $subject;
    public string $message;
    public Message $messageModel;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $subject, string $type, Message $messageModel)
    {
        $this->message = $message;
        $this->subject = $subject;
        $this->type = $type;
        $this->messageModel = $messageModel;
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
            'message_id' => $this->messageModel->id,
            'user_id' => $this->messageModel->sender_id,
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'message',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'message',
            'created_at' => $this->messageModel->created_at,
        ]);
    }
}
