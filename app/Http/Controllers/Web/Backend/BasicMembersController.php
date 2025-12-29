<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BasicMembersController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Membership::with('user')->where('membership_type', 'basic')->latest()->get();

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
                    return $data->user->shopInfo->products()->count() ?? 0;
                })
                ->addColumn('shop_name', function ($data) {
                    $shopName = $data->user->shopInfo->shop_name ?? 'N/A';
                    $frontendUrl = env('FRONTEND_BASE_URL');

                    $viewType = 'customer';
                    $userId = $data->user->id;
                    $listingId = $data->user->shopInfo->id;

                    return "<a href='{$frontendUrl}/shop-details?view={$viewType}&id={$userId}&listing_id={$listingId}' target='_blank'>{$shopName}</a>";
                })
                ->addColumn('created_at', function ($data) {
                    return $data->created_at->format('d M Y h:i A');
                })
                ->addColumn('avatar', function ($data) {
                    $url = asset($data->user->avatar);
                    if (empty($data->user->avatar)) {
                        $url = asset('backend/images/placeholder/image_placeholder.png');
                    }
                    return '<img src="' . $url . '" class="img-fluid rounded object-fit-cover" style="width: 50px;">';
                })
                ->addColumn('action', function ($data) {
                    $btn = '';
                    if ($data->user->status == "inactive") {
                        $btn .= '<a href="#" onclick="toggleSuspend(' . $data->user->id . ', \'Unsuspend\')" 
                        class="text-white btn btn-danger btn-sm" title="Unsuspend">
                        Unsuspend
                        </a>';
                    } else {
                        $btn .= '<a href="#" onclick="toggleSuspend(' . $data->user->id . ', \'Suspend\')" 
                        class="text-white btn btn-success btn-sm" title="Suspend">
                        Suspend
                        </a>';
                    }

                    $btn .= ' <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" 
                    class="text-white btn btn-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                    </a>';

                    return '<div class="text-center">' . $btn . '</div>';
                })

                ->rawColumns(['avatar', 'action', 'owner_name', 'email', 'total_products', 'shop_name', 'created_at'])
                ->make(true);
        }
        return view('backend.layouts.basic_members.index');
    }

    public function suspendToggle($id)
    {
        $user = User::findOrFail($id);

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return response()->json([
            'success' => true,
            'status'  => ucfirst($user->status) // Active / Inactive
        ]);
    }
}
