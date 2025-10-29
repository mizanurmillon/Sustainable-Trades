<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class GetConversationController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $name = $request->query('name');
        $unreadOnly = $request->query('unread');
        $sent = $request->query('sent');

        $query = Conversation::query()
            ->with([
                'participants' => function ($q) use ($user) {
                    $q->where('participant_id', '!=', $user->id)
                        ->where('participant_type', get_class($user))
                        ->with([
                            'participant' => function ($sub) {
                                $sub->select('id', 'first_name', 'last_name', 'avatar');
                            }
                        ])
                        ->take(3);
                },
                'lastMessage',
            ])
            ->whereHas('participants', function ($q) use ($user) {
                $q->where('participant_type', get_class($user))
                    ->where('participant_id', $user->id);
            });

        // Filter by participant name
        if (!empty($name)) {
            $query->whereHas('participants.participant', function ($q) use ($name, $user) {
                $q->where('id', '!=', $user->id)
                    ->where(function ($subQuery) use ($name) {
                        $subQuery->where('first_name', 'like', '%' . $name . '%')
                            ->orWhere('last_name', 'like', '%' . $name . '%');
                    });
            });
        }

        // Filter only unread conversations
        if ($unreadOnly) {
            $query->whereHas('messages', function ($q) use ($user) {
                $q->where('receiver_id', $user->id)
                    ->where('is_read', 0);
            });
        }

        // Filter conversations with messages sent by the user
        if ($sent) $query->whereHas('sentMessages', fn($q) => $q->where('sender_id', $user->id));

        // Final conversations query
        $conversations = $query->withCount('unreadMessages')
            ->where('type', '!=', 'order')
            ->latest('updated_at')
            ->get();

        $response = [
            'total_conversations' => $conversations->count(),
            'self' => [
                'id' => $user->id,
                'first_name' => $user->first_name ?? null,
                'last_name' => $user->last_name ?? null,
                'avatar' => $user->avatar ?? null,
            ],
            'conversations' => $conversations,
        ];

        return $this->success($response, 'Conversations fetched successfully.', 200);
    }
}
