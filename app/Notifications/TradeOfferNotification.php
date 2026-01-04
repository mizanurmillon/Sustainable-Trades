<?php

namespace App\Notifications;

use App\Models\TradeOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TradeOfferNotification extends Notification
{
    use Queueable;

    protected $subject;
    public string $message;
    public TradeOffer $tradeOffer;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $subject, string $type, TradeOffer $tradeOffer)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->type = $type;
        $this->tradeOffer = $tradeOffer;
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
            'tradeOffer' => $this->tradeOffer->id,
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'trade_offer',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => $this->subject,
            'message' => $this->message,
            'type' => $this->type,
            'status' => 'trade_offer',
            'created_at' => $this->tradeOffer->created_at,
        ]);
    }
}
