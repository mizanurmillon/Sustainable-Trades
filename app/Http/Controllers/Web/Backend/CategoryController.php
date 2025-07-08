<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Category::latest()->get();
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('name', 'LIKE', "%$searchTerm%");
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {

                    $url = asset($data->image);

                    if(empty($data->image)){
                        $url = asset('backend/images/placeholder/image_placeholder.png');
                    }

                    return '<img src="' . $url . '">';
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
                              <a href="' . route('admin.categories.edit', ['id' => $data->id]) . '" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div></div>';
                })
                ->rawColumns(['image','action', 'status'])
                ->make(true);
        }
        return view('backend.layouts.category.index');
    }

    public function create()
    {
        return view('backend.layouts.category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
        ]);

        if ($request->hasFile('image')) {
            $image                        = $request->file('image');
            $imageName                    = uploadImage($image, 'categories');
        }

        $category = new Category();
        $category->name = $request->name;
        $category->image = $imageName;

        $category->save();

        return redirect()->route('admin.categories.index')->with('t-success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $data = Category::findOrFail($id);
        return view('backend.layouts.category.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
        ]);

        $category = Category::findOrFail($id);

        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if(file_exists(public_path($category->image))){
                unlink(public_path($category->image));
            }
            $image = $request->file('image');
            $imageName = uploadImage($image, 'categories');
        }else{
            // If no new image is uploaded, keep the old image
            $imageName = $category->image;
        }

        $category->name = $request->name;
        $category->image = $imageName;

        $category->save();

        return redirect()->route('admin.categories.index')->with('t-success', 'Category updated successfully.');
    }

    public function status(int $id): JsonResponse {
        $data = Category::findOrFail($id);
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
        $data = Category::findOrFail($id);

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
