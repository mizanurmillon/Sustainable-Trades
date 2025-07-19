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

        $conversations = Conversation::query()
            ->with([
                'participants' => function ($query) use ($user, $name) {
                    $query->where('participant_id', '!=', $user->id)
                        ->where('participant_type', get_class($user))
                        ->with(['participant' => function ($q) use ($name) {
                            $q->select('id', 'first_name','last_name','avatar');
                        }])
                        ->take(3);
                },
                'lastMessage'
            ])
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('participant_type', get_class($user))
                    ->where('participant_id', $user->id);
            })
            ->withCount('unreadMessages')
            ->latest('updated_at')
            ->paginate(15);

        $response = [
            'total_conversations' => $conversations->count(),
            'self' => $user->only(['id', 'first_name', 'last_name', 'avatar']),
            'conversations' => $conversations,
        ];

        return $this->success($response, 'Conversations fetched successfully.', 200);
    }
}
