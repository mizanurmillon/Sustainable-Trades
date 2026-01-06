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

        $notifications = $user->notifications()
            ->select('id', 'notifiable_id', 'data', 'read_at', 'created_at')
            ->latest()
            ->when($request->status, function ($q) use ($request) {
                $q->where('data->status', $request->status);
            })
            ->paginate(10);

        // Collect user IDs
        $userIds = $notifications->pluck('data.user_id')->unique()->filter();

        $users = User::whereIn('id', $userIds)
            ->select('id', 'first_name', 'last_name', 'avatar')
            ->get()
            ->keyBy('id');

        // 👇 pagination-safe transform
        $notifications->through(function ($notification) use ($users) {
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

        // Counts
        $allNotify = $user->notifications()->toBase()->get();
        $counts = [
            'all'         => $allNotify->count(),
            'orders'      => $allNotify->where('data.type', 'order')->count(),
            'messages'    => $allNotify->where('data.type', 'message')->count(),
            'trade_offer' => $allNotify->where('data.type', 'trade_offer')->count(),
        ];

        return $this->success([
            'counts' => $counts,
            'notifications' => $notifications, // 👈 paginator intact
        ], 'Notifications fetch Successful!');
    }


    public function todayNotifications()
    {
        $user = auth()->user();
        if (!$user) return $this->error([], 'User not found', 200);

        $notifications = $user->notifications()
            ->select('id', 'notifiable_id', 'data', 'read_at', 'created_at')
            ->latest()
            ->whereDate('created_at', now()->toDateString())
            ->paginate(10);

        $userIds = $notifications->pluck('data.user_id')->unique()->filter();

        $users = User::whereIn('id', $userIds)
            ->select('id', 'first_name', 'last_name', 'avatar')
            ->get()
            ->keyBy('id');

        $notifications->through(function ($notification) use ($users) {
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

        if ($notifications->isEmpty()) {
            return $this->error([], 'No Notifications for Today', 200);
        }

        return $this->success($notifications, "Today's Notifications fetch Successful!");
    }

    public function clearAllNotifications()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 200);
        }

        $user->notifications()->delete();

        return $this->success([], 'All notifications cleared successfully!', 200);
    }

    public function clearNotification($id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 200);
        }

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return $this->error([], 'Notification not found', 200);
        }

        $notification->delete();

        return $this->success([], 'Notification cleared successfully!', 200);
    }
}
