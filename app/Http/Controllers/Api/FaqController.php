<?php

namespace App\Http\Controllers\Api;

use App\Enum\Page;
use App\Models\Cms;
use App\Models\Faq;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    use ApiResponse;

    /**
     * Fetch FAQs
     *
     * @return \Illuminate\Http\JsonResponse  JSON response with success or error.
     */
    public function FaqAll()
    {
        $faq = Faq::where('status', 'active')->get();

        $cms = Cms::where('page_name', Page::FAQ->value)->select(['id','image'])->first();

        if ($faq->isEmpty()) {
            return $this->error([], 'Faq not found', 200);
        }

        $faq = [
            'banner' => $cms,
            'faqs' => $faq
        ];

        return $this->success($faq, 'Faq fetch Successful!', 200);
    }
}
