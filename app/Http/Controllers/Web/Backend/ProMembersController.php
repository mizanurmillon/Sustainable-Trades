<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProMembersController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Membership::with('user')->where('membership_type', 'pro')->latest()->get();

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('shop_name', 'LIKE', "%$searchTerm%");
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('owner_name', function ($data) {
                    $first = $data->user->first_name ?? '';
                    $last = $data->user->last_name ?? '';
                    return trim($first . ' ' . $last);
                })
                ->addColumn('email', function ($data) {
                    return $data->user->email ?? 'N/A';
                })
                ->addColumn('phone', function ($data) {
                    return $data->user->phone ?? 'N/A';
                })
                ->addColumn('total_products', function ($data) {
                    return $data->user->shopInfo->products()->count();
                })
                ->addColumn('shop_name', function ($data) {
                    return $data->user->shopInfo->shop_name ?? 'N/A';
                })
                ->addColumn('avatar', function ($data) {
                    $url = asset($data->user->avatar);
                    if (empty($data->user->avatar)) {
                        $url = asset('backend/images/placeholder/image_placeholder.png');
                    }
                    return '<img src="' . $url . '" class="img-fluid rounded object-fit-cover" style="width: 50px;">';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="text-center"><div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="' . route('admin.categories.edit', ['id' => $data->id]) . '" class="text-white btn btn-warning" title="Suspend">
                              Suspend
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div></div>';
                })

                ->rawColumns(['avatar', 'action', 'owner_name', 'email', 'total_products', 'shop_name'])
                ->make(true);
        }
        return view('backend.layouts.pro_members.index');
    }
}
