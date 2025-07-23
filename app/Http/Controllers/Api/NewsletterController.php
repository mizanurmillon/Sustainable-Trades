<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    use ApiResponse;
    
    public function subscribe(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletters,email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $data = Newsletter::create([
            'email' => $request->email,
        ]);

        if (!$data) {
            return $this->error([], 'Subscription failed', 500);
        }
        

        return $this->success($data,'Subscription successful',200);
    }
}
