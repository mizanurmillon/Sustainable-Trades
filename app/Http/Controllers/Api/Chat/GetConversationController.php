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

        $name = request()->query('name') ?? null;
        $unread = $request->has('unread');

        $sent = $request->has('sent');

        $conversations = Conversation::query()
            ->with([
                'participants' => function ($query) use ($user, $name) {
                    $query->where('participant_id', '!=', $user->id)
                        ->where('participant_type', get_class($user))
                        ->with(['participant' => function ($q) use ($name) {
                            $q->select('id', 'first_name', 'last_name', 'avatar');
                        }])
                        ->take(3);
                },
                'lastMessage'
            ])
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('participant_type', get_class($user))
                    ->where('participant_id', $user->id);
            })
            ->when($name, function ($query, $name) {
                $query->whereHas('participants.participant', function ($q) use ($name) {
                    $q->where(function ($subQuery) use ($name) {
                        $subQuery->where('first_name', 'LIKE', "%$name%")
                            ->orWhere('last_name', 'LIKE', "%$name%");
                    });
                });
            })
            ->when($unread, function ($query) {
                $query->whereHas('messages', function ($q) {
                    $q->where('is_read', false);
                });
            })
            ->when($sent, function ($query) use ($user) {
                $query->whereHas('messages', function ($q) use ($user) {
                    $q->where('sender_id', $user->id);
                });
            })
            ->withCount([
                'messages as unread_message_count' => function ($query) use ($user) {
                    $query->where('is_read', false)
                        ->where('sender_id', '!=', $user->id);
                }
            ])
            ->latest('updated_at')
            ->get();

        $response = [
            'total_conversations' => $conversations->count(),
            'self' => $user->only(['id', 'first_name', 'last_name', 'avatar']),
            'conversations' => $conversations,
        ];

        return $this->success($response, 'Conversations fetched successfully.', 200);
    }
}
