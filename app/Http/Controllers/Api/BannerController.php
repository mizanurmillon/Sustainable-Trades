<?php

namespace App\Http\Controllers\Api;

use App\Models\Banner;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\HowItWork;

class BannerController extends Controller
{
    use ApiResponse;
    
    public function banners() 
    {
        $data = Banner::latest()->get();

        if($data->isEmpty()) {
            return $this->error([], 'No banners found', 404);
        }

        return $this->success($data, 'Banners retrieved successfully', 200);
    }

    public function howItWorks()
    {
        $data = HowItWork::latest()->get();

        if($data->isEmpty()) {
            return $this->error([], 'No how it works found', 404);
        }

        return $this->success($data, 'How it works retrieved successfully', 200);
    }
}
