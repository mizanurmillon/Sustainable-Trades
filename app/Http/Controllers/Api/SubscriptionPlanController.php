<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    use ApiResponse;
     
    public function subscriptions(Request $request)
    {
       $query = SubscriptionPlan::with('subscription_benefit'); 

       if ($request->has('interval')) {
            $query->where('interval', $request->interval);
       }
       
       $data = $query->get();

        if ($data->isEmpty()) {
            return $this->error([], 'Data not found', 200);
        }

        return $this->success($data,'Data fetched successfully',200);
    }
}
