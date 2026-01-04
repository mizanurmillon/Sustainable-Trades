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
        if (!$user) return $this->error([], 'User not found', 200);

        // 1. Get all notifications first
        $notifications = $user->notifications()
            ->select('id', 'notifiable_id', 'data', 'read_at', 'created_at')
            ->latest()
            ->when($request->status, function ($q) use ($request) {
                return $q->where('data->status', $request->status);
            })
            ->get();

        // 2. Collect all unique user IDs to fetch them in ONE query
        $userIds = $notifications->pluck('data.user_id')->unique()->filter();
        $users = User::whereIn('id', $userIds)
            ->select('id', 'first_name', 'last_name', 'avatar')
            ->get()
            ->keyBy('id');

        // 3. Map the data
        $formattedNotifications = $notifications->map(function ($notification) use ($users) {
            $userData = $users->get($notification->data['user_id'] ?? null);

            return [
                'id' => $notification->id,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'user' => $userData ? [
                    'id' => $userData->id,
                    'name' => "{$userData->first_name} {$userData->last_name}",
                    'avatar' => $userData->avatar,
                ] : null,
            ];
        });

        // 4. Counts (Consider caching these if you have many users)
        $allNotifs = $user->notifications()->toBase()->get();
        $counts = [
            'all'         => $allNotifs->count(),
            'orders'      => $allNotifs->where('data.type', 'order')->count(),
            'messages'    => $allNotifs->where('data.type', 'message')->count(),
            'trade_offer' => $allNotifs->where('data.type', 'trade_offer')->count(),
        ];

        return $this->success([
            'counts' => $counts,
            'notifications' => $formattedNotifications,
        ], 'Notifications fetch Successful!');
    }
}
