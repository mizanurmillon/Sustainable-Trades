<?php

namespace App\Http\Controllers\Api;

use App\Enum\Page;
use App\Models\Cms;
use App\Models\Tutorial;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TutorialsController extends Controller
{
    use ApiResponse;
    
    public function tutorials(Request $request)
    {
        $cms = Cms::where('page_name', Page::TUTORIALS->value)->select(['id','image'])->first();

        $query = Tutorial::where('status', 'active')->latest()->get();

        if($request->has('type'))
        {
            $query = $query->where('type', $request->type);
        }

        $data = [
            'banner' => $cms,
            'tutorials' => $query
        ];

        return $this->success($data, 'Tutorials fetched successfully.', 200);
    }
}
