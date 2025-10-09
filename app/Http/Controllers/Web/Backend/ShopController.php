<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\ShopInfo;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ShopController extends Controller
{
    public function index(Request $request)
    {


        if ($request->ajax()) {


            $data = ShopInfo::with('user', 'products')->withCount('products')->latest()->get();

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('shop_name', 'LIKE', "%$searchTerm%");
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('shop_name', function ($data) {
                    $shopName = $data->user->shopInfo->shop_name ?? 'N/A';

                    return "<a href='#' target='_blank'>{$shopName}</a>";
                })
                ->addColumn('total_products', function ($data) {
                    return $data->products_count ?? 0;
                })
                ->addColumn('owner_name', function ($data) {
                    $first = $data->user->first_name ?? '';
                    $last = $data->user->last_name ?? '';
                    return trim($first . ' ' . $last);
                })
                ->addColumn('city', function ($data) {
                    return $data->shop_city ?? 'N/A';
                })
                ->addColumn('image', function ($data) {
                    $url = asset($data->shop_image);
                    if (empty($data->shop_image)) {
                        $url = asset('backend/images/placeholder/image_placeholder.png');
                    }
                    return '<img src="' . $url . '" class="img-fluid rounded object-fit-cover" style="width: 100px; height: 70px;">';
                })
                ->addColumn('is_featured', function ($data) {
                    $status = ' <div class="form-check form-switch">';
                    $status .= ' <input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status"';
                    if ($data->is_featured == true) {
                        $status .= "checked";
                    }
                    $status .= '><label for="customSwitch' . $data->id . '" class="form-check-label" for="customSwitch"></label></div>';

                    return $status;
                })

                ->rawColumns(['image', 'action', 'shop_name', 'is_featured', 'total_products', 'city'])
                ->make(true);
        }
        return view("backend.layouts.shops.index");
    }

    public function featured(Request $request, $id)
    {
        $data = ShopInfo::findOrFail($id);
        if ($data->is_featured == false) {
            $data->is_featured = true;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'This Shop is Featured Successfully.',
                'data'    => $data,
            ]);
        } else {
            $data->is_featured = false;
            $data->save();

            return response()->json([
                'success' => false,
                'message' => 'This Shop is no longer Featured.',
                'data'    => $data,
            ]);
        }
    }
}
