<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\OurMissoin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OurMissoinController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = OurMissoin::latest()->get();
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('name', 'LIKE', "%$searchTerm%");
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('description', function ($data) {
                    $description       = $data->description;
                    $short_description = strlen($description) > 60 ? substr($description, 0, 60) . '...' : $description;
                    return '<p>' . $short_description . '</p>';
                })
                ->addColumn('image', function ($data) {

                    $url = asset($data->image);

                    if(empty($data->image)){
                        $url = asset('backend/images/placeholder/image_placeholder.png');
                    }

                    return '<img src="' . $url . '" class="img-fluid" width="50px">';
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
                              <a href="' . route('admin.our_missions.edit', ['id' => $data->id]) . '" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div></div>';
                })
                ->rawColumns(['description', 'action','status','image'])
                ->make(true);
        }
        return view("backend.layouts.our_mission.index");
    }

    public function create()
    {
        return view("backend.layouts.our_mission.create");
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'description' => 'nullable|string|max:2000',
        ]);

        if($request->hasFile('image')) {
            $image                        = $request->file('image');
            $imageName                    = uploadImage($image, 'our_missions');
        }

        OurMissoin::create([
            'name' => $request->name,
            'image' => $imageName,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.our_missions.index')->with('t-success', 'Our Mission created successfully.');
    }

    public function edit($id)
    {
        $data = OurMissoin::findOrFail($id);
        if(!$data) {
            abort(404);
        }
        return view("backend.layouts.our_mission.edit", compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'description' => 'nullable|string|max:2000',
        ]);

        $data = OurMissoin::findOrFail($id);

        if($request->hasFile('image')) {
            if(file_exists(public_path($data->image))){
                unlink(public_path($data->image));
            }
            $image                        = $request->file('image');
            $imageName                    = uploadImage($image, 'our_missions');
        }else{
            $imageName = $data->image;
        }

        $data->name = $request->name;
        $data->image = $imageName;
        $data->description = $request->description;

        $data->save();
        return redirect()->route('admin.our_missions.index')->with('t-success', 'Our Mission updated successfully.');
    }

    public function status(int $id): JsonResponse {
        $data = OurMissoin::findOrFail($id);
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

    public function destroy(int $id): JsonResponse {
        $data = OurMissoin::findOrFail($id);

        if(file_exists(public_path($data->image))){
            unlink(public_path($data->image));
        }

        $data->delete();
        
        return response()->json([
            't-success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }
}
