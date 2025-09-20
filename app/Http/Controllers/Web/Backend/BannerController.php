<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Banner::latest()->get();
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('title', 'LIKE', "%$searchTerm%");
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {

                    $url = asset($data->image);

                    if (empty($data->image)) {
                        $url = asset('backend/images/placeholder/image_placeholder.png');
                    }

                    return '<img src="' . $url . '" class="img-fluid" style="width: 100px; height: auto;">';
                })
                ->addColumn('description', function ($data) {
                    $description       = $data->description;
                    $short_description = strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                    return '<p>' . $short_description . '</p>';
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
                              <a href="' . route('admin.banners.edit', ['id' => $data->id]) . '" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div></div>';
                })
                ->rawColumns(['image', 'action', 'status', 'description'])
                ->make(true);
        }

        return view('backend.layouts.banners.index');
    }

    public function create()
    {
        return view('backend.layouts.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sub_title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:51200', // 50MB max
        ]);

        if($request->hasFile('image')) {
            $image                        = $request->file('image');
            $imageName                    = uploadImage($image, 'banners');
        }

        Banner::create([
            'title' => $request->title,
            'sub_title' => $request->sub_title,
            'description' => $request->description,
            'image' => $imageName,
        ]);

        return redirect()->route('admin.banners.index')->with('t-success', 'Banner created successfully.');
    }

    public function edit($id)
    {
        $data = Banner::findOrFail($id);
        return view('backend.layouts.banners.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sub_title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:51200', // 50MB max
        ]);

        $data = Banner::findOrFail($id);

        if($request->hasFile('image')) {
            if(file_exists(public_path($data->image))){
                unlink(public_path($data->image));
            }
            $image                        = $request->file('image');
            $imageName                    = uploadImage($image, 'banners');
        }else{
            $imageName = $data->image;
        }

        $data->title = $request->title;
        $data->sub_title = $request->sub_title;
        $data->description = $request->description;
        $data->image = $imageName;

        $data->save();
        return redirect()->route('admin.banners.index')->with('t-success', 'Banner updated successfully.');
    }

     public function status(int $id): JsonResponse {
        $data = Banner::findOrFail($id);
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
        $data = Banner::findOrFail($id);

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
