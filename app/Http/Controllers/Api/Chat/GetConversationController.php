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

        $conversations = Conversation::query()
            ->with([
                'participants' => function ($query) use ($user, $name) {
                    $query->where('participant_id', '!=', $user->id)
                        ->where('participant_type', get_class($user))
                        ->with(['participant' => function ($q) use ($name) {
                            $q->select('id', 'first_name','last_name', 'avatar');
                        }])
                        ->take(3);
                },
                'lastMessage',
            ])
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('participant_type', get_class($user))
                    ->where('participant_id', $user->id);
            })
            ->when($name, function ($query, $name) {
                $query->whereHas('participants', function ($query) use ($name) {
                    $query->whereHas('participant', function ($query) use ($name) {
                        $query->where('first_name', 'like', "%$name%")
                            ->orWhere('last_name', 'like', "%$name%");
                    });
                });
            })
            ->withCount('unreadMessages')
            ->latest('updated_at')
            ->get();

        $response = [
            'total_conversations' => $conversations->count(),
            'self' => $user->only(['id', 'name', 'avatar']),
            'conversations' => $conversations,
        ];

        return $this->success($response, 'Conversations fetched successfully.', 200);
    }
}
