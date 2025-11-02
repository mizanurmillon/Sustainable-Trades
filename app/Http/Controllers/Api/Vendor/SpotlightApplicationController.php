<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SpotlightApplication;
use Illuminate\Support\Facades\Validator;

class SpotlightApplicationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = SpotlightApplication::with('user:id,name,email','user.shopInfo:id,user_id,shop_name')->where('status', 'approved');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $data = $query->latest()->get();

        if ($data->isEmpty()) {
            return $this->error([], 'Data not found', 200);
        }

        return $this->success($data, 'Spotlight applications retrieved successfully', 200);
        
    }
     
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'shop_name' => 'required|string|max:255',
            'shop_description'=> 'required|string|max:5000',
            'sustainability_important'=> 'required|string|max:3000',
            'what_impact'=> 'required|string|max:3000',
            'community_engagement'=> 'required|string|max:3000',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = $request->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        if($request->hasFile('image')) {
            $image                        = $request->file('image');
            $imageName                    = uploadImage($image, 'spotlight_applications');
        }

        $data = SpotlightApplication::create([
            'name'=> $request->name,
            'image'=> $imageName,
            'user_id'=> $user->id,
            'shop_name'=> $request->shop_name,
            'shop_description'=> $request->shop_description,
            'sustainability_important' => $request->sustainability_important,
            'what_impact'=> $request->what_impact,
            'community_engagement'=> $request->community_engagement,
        ]);

        if(!$data) {
            return $this->error([], 'Something went wrong', 500);
        }

        return $this->success($data, 'Spotlight application created successfully');
    }
}
