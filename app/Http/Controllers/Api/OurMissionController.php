<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OurMissoin;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OurMissionController extends Controller
{
    use ApiResponse;
    
    public function ourMission()
    {
        $data = OurMissoin::where('status','active')->get();

        if($data->isEmpty()) {
            return $this->error([],'Data not found',200);
        }

        return $this->success($data,'Data fetched successfully',200);
        
    }
}
