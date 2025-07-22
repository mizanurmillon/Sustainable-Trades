<?php

namespace App\Http\Controllers\Api\Chat;

use App\Models\User;
use App\Traits\ApiResponse;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GetMessageController extends Controller
{
    use ApiResponse;

    /**
     * Get chat messages based on receiver ID or conversation ID.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'receiver_id' => ['nullable', 'required_without:conversation_id', 'integer'],
            'conversation_id' => ['nullable', 'required_without:receiver_id', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $name = $request->query('name');

        $receiver_id = null;
        if ($request->query('receiver_id')) {
            $receiver = User::find($request->query('receiver_id'));
            if (!$receiver) {
                return $this->error([], 'Receiver not found', 404);
            }
            $receiver_id = $receiver->id;
        }

        $conversation_id = $request->query('conversation_id');

        // Conversation logic
        $conversation = $this->getConversation($user, $receiver_id, $conversation_id);

        if (!$conversation) {
            return $this->error([], 'Conversation not found', 404);
        } else {
            if ($conversation->type == 'private' && !$receiver_id) {
                return $this->error([], 'Receiver ID is required for private conversations', 422);
            }
        }

        if (!$conversation_id) {
            $conversation->load([
                'participants' => function ($query) use ($user) {
                    $query->where('participant_id', '!=', $user->id)
                        ->where('participant_type', get_class($user))
                        ->with(['participant' => function ($q) {
                            $q->select('id', 'first_name', 'last_name', 'avatar');
                        }])
                        ->take(3);
                },
            ]);
        } else {
            $conversation->load([
                'participants' => function ($query) use ($user) {
                    $query->where('participant_id', '!=', $user->id)
                        ->where('participant_type', get_class($user))
                        ->with(['participant' => function ($q) {
                            $q->select('id', 'first_name', 'last_name', 'avatar');
                        }])
                        ->take(3);
                }
            ], 'group');
        }

        // Mark messages as read
        $conversation->messages()
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $conversation->messages()
            ->with(['sender:id,first_name,last_name,avatar', 'parentMessage', 'attachments',])
            ->orderBy('created_at', 'desc')
            ->withTrashed()
            ->paginate(100);


        return $this->success([
            'conversation' => $conversation,
            'messages' => $messages,
        ], 'Chat messages retrieved successfully', 200);
    }


    /**
     * Get or create a conversation based on the provided parameters.
     *
     * @param User $user
     * @param int|null $receiver_id
     * @param int|null $conversation_id
     * @return Conversation|null
     */
    private function getConversation(User $user, $receiver_id = null, $conversation_id = null)
    {
        if ($conversation_id) {
            $conversation = Conversation::where('id', $conversation_id)
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('participant_id', $user->id)
                        ->where('participant_type', User::class);
                })
                ->where('type', 'group')
                ->first();

            if (!$conversation) {
                return false;
            } else {
                return $conversation;
            }
        } elseif ($receiver_id) {
            $receiver = User::find($receiver_id);
            if (!$receiver) {
                return $this->error([], 'Receiver not found', 404);
            }

            if ($receiver->id === $user->id) {
                $conversation = Conversation::whereHas('participants', function ($q) use ($user) {
                    $q->where('participant_id', $user->id)
                        ->where('participant_type', User::class);
                })
                    ->where('type', 'self')
                    ->first();

                if (!$conversation) {
                    $conversation = Conversation::create([
                        'type' => 'self',
                    ]);

                    $conversation->participants()->createMany([
                        [
                            'participant_id' => $user->id,
                            'participant_type' => User::class,
                        ],
                    ]);
                    return $conversation;
                } else {
                    return $conversation;
                }
            }

            $conversation = Conversation::whereHas('participants', function ($q) use ($user) {
                $q->where('participant_id', $user->id)
                    ->where('participant_type', User::class);
            })
                ->whereHas('participants', function ($q) use ($receiver) {
                    $q->where('participant_id', $receiver->id)
                        ->where('participant_type', User::class);
                })
                ->where('type', 'private')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'type' => 'private',
                ]);

                $conversation->participants()->createMany([
                    [
                        'participant_id' => $receiver->id,
                        'participant_type' => User::class,
                    ],
                    [
                        'participant_id' => $user->id,
                        'participant_type' => User::class,
                    ],
                ]);
                return $conversation;
            } else {
                return $conversation;
            }
        } else {
            return $this->error([], 'Either receiver_id or conversation_id is required', 422);
        }
    }
}
