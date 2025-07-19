<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId;
    public $receiverId;
    public $conversation;
    public $unreadMessageCount;

    /**
     * Create a new event instance.
     */
    public function __construct($senderId, $receiverId, $conversation, $unreadMessageCount)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->conversation = $conversation;
        $this->unreadMessageCount = $unreadMessageCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation-channel.' . $this->receiverId),
        ];
    }

    /**
     * Function : broadcastWith
     * @return array
     * */
    public function broadcastWith(): array
    {
        return [
            'senderId'   => $this->senderId,
            'receiverId' => $this->receiverId,
            'conversation' => $this->conversation,
            'unreadMessageCount' => $this->unreadMessageCount,
        ];
    }
}
