<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\SpotlightApplication;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SpotlightApplicationNotification extends Notification
{
    use Queueable;
    protected $subject;
    public string $message;
    public SpotlightApplication $application;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $subject, string $type, SpotlightApplication $application)
    {
        $this->message = $message;
        $this->subject = $subject;
        $this->application = $application;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database','broadcast'];
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
            'application'=> $this->application->id,
            'subject'=> $this->subject,
            'message'=> $this->message,
            'type'=> $this->type,
            'status' => 'spotlight-application',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject'=> $this->subject,
            'message'=> $this->message,
            'type'=> $this->type,
            'status' => 'spotlight-application',
            'created_at' => $this->application->created_at,
        ]);
    }
}
