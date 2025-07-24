<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class GetNotificationController extends Controller
{
    use ApiResponse;

    public function getNotifications(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 200);
        }

        $query = $user->notifications()->select('id', 'notifiable_id', 'data', 'read_at', 'created_at')->latest();

        if ($request->has('status')) {
            $query->where('data->status', $request->status); 
        }

        $data = $query->get();

        if ($data->isEmpty()) {
            return $this->error([], 'Notifications not found', 200);
        }

        return $this->success($data, 'Notifications fetch Successful!', 200);
    }
}
