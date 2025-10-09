<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SustainableShoppersController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = User::where('role', 'customer')->latest()->get();

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('first_name', 'LIKE', "%$searchTerm%");
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('owner_name', function ($data) {
                    $first = $data->first_name ?? '';
                    $last = $data->last_name ?? '';
                    return trim($first . ' ' . $last);
                })
                ->addColumn('email', function ($data) {
                    return $data->email ?? 'N/A';
                })
                ->addColumn('phone', function ($data) {
                    return $data->phone ?? 'N/A';
                })
                ->addColumn('role', function ($data) {
                    return '<span class="badge bg-primary text-white">' . $data->role . '</span>';
                })
                ->addColumn('created_at', function ($data) {
                    return $data->created_at->format('d M Y h:i A');
                })
                ->addColumn('avatar', function ($data) {
                    $url = asset($data->avatar);
                    if (empty($data->avatar)) {
                        $url = asset('backend/images/placeholder/image_placeholder.png');
                    }
                    return '<img src="' . $url . '" class="img-fluid rounded object-fit-cover" style="width: 50px;">';
                })
                ->addColumn('status', function ($data) {
                    $status = ' <div class="form-check form-switch">';
                    $status .= ' <input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status"';
                    if ($data->status == "active") {
                        $status .= "checked";
                    }
                    $status .= '><label for="customSwitch' . $data->id . '" class="form-check-label" for="customSwitch"></label></div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="text-center"><div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div></div>';
                })

                ->rawColumns(['avatar', 'action', 'owner_name', 'email', 'status', 'role', 'created_at'])
                ->make(true);
        }

        return view('backend.layouts.sustainable_shoppers.index');
    }

    public function status(int $id): JsonResponse
    {
        $data = User::findOrFail($id);
        if ($data->status == 'inactive') {
            $data->status = 'active';
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Published Successfully.',
                'data'    => $data,
            ]);
        } else {
            $data->status = 'inactive';
            $data->save();

            return response()->json([
                'success' => false,
                'message' => 'Unpublished Successfully.',
                'data'    => $data,
            ]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $data = User::findOrFail($id);

        if (!empty($data->avatar)) {
            $avatarPath = public_path($data->avatar);

            if (file_exists($avatarPath) && is_file($avatarPath)) {
                unlink($avatarPath);
            }
        }

        $data->delete();

        return response()->json([
            't-success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }
}
