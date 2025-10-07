<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetNotificationController extends Controller
{
    use ApiResponse;

    public function getNotifications(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 200);
        }

        $query = $user->notifications()
            ->select('id', 'notifiable_id', 'data', 'read_at', 'created_at')
            ->latest();

        if ($request->has('status')) {
            $query->where('data->status', $request->status);
        }

        $data = $query->get()->map(function ($notification) {
            $notifiableUser = User::select('id', 'first_name', 'last_name', 'avatar')
                ->find($notification->notifiable_id);

            return [
                'id' => $notification->id,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'user' => $notifiableUser ? [
                    'id' => $notifiableUser->id,
                    'name' => $notifiableUser->first_name . ' ' . $notifiableUser->last_name,
                    'avatar' => $notifiableUser->avatar,
                ] : null,
            ];
        });

        if ($data->isEmpty()) {
            return $this->error([], 'Notifications not found', 200);
        }

        return $this->success($data, 'Notifications fetch Successful!', 200);
    }
}
