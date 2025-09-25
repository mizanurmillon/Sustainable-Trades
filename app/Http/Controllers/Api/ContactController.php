<?php

namespace App\Http\Controllers\Api;

use App\Enum\Page;
use App\Models\Cms;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    use ApiResponse;
    
    public function contact()
    {
        $cms = Cms::where('page_name', Page::CONTACT->value)->select(['id','image','description'])->first();

        if (!$cms) {
            return $this->error([], 'Contact page not found', 404);
        }

        return $this->success($cms, 'Contact page fetched successfully', 200);
    }

    public function termsAndConditions()
    {
        $cms = Cms::where('page_name', Page::TERMS_AND_CONDITIONS->value)->select(['id','description'])->first();

        if (!$cms) {
            return $this->error([], 'Terms and Conditions page not found', 404);
        }

        return $this->success($cms, 'Terms and Conditions page fetched successfully', 200);
    }

    public function InfringementReport()
    {
        $cms = Cms::where('page_name', Page::INFRINGEMENT_REPORT->value)->select(['id','description'])->first();

        if (!$cms) {
            return $this->error([], 'Infringement Report page not found', 404);
        }

        return $this->success($cms, 'Infringement Report page fetched successfully', 200);
    }
}
