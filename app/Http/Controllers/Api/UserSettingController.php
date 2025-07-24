<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSettingController extends Controller
{
    use ApiResponse;
    
    public function pushNotificationSetting(Request $request)
    {
        // Validate the request if needed
        $validator = Validator::make($request->all(), [
            'is_push_notifications' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), "Validation Error", 422);
        }

        // Get the authenticated user
    
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User Not Found', 404);
        }

        // Update the user's push notification setting
        $user->is_push_notifications = $request->input('is_push_notifications');
        $user->save();
        return $this->success($user, 'Push notification setting updated successfully', 200);
       
    }

    public function cookiesSetting(Request $request)
    {
        // Validate the request if needed
        $validator = Validator::make($request->all(), [
            'is_cookies' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), "Validation Error", 422);
        }

        // Get the authenticated user
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User Not Found', 404);
        }

        // Update the user's cookies setting
        $user->is_cookies = $request->input('is_cookies');
        $user->save();
        
        return $this->success($user, 'Cookies setting updated successfully', 200);
    }
}
