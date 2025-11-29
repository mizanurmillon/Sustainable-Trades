<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
       if ($request->ajax()) {

            $data = SubCategory::latest()->get();
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('sub_category_name', 'LIKE', "%$searchTerm%");
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category_name', function ($data) {
                    return $data->category ? $data->category->name : 'N/A';
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
                              <a href="' . route('admin.sub_categories.edit', ['id' => $data->id]) . '" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                            </div></div>';
                })
                ->rawColumns(['category_name','action', 'status'])
                ->make(true);
        }
        return view('backend.layouts.sub_categories.index');
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->get();

        return view('backend.layouts.sub_categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_category_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        SubCategory::create([
            'sub_category_name' => $request->sub_category_name,
            'category_id' => $request->category_id,
            'status' => 'active',
        ]);

        return redirect()->route('admin.sub_categories.index')->with('t-success', 'Sub Category created successfully.');
    }

    public function edit($id)
    {
        $data = SubCategory::findOrFail($id);
        $categories = Category::where('status', 'active')->get();

        return view('backend.layouts.sub_categories.edit', compact('data', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sub_category_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subCategory = SubCategory::findOrFail($id);
        $subCategory->sub_category_name = $request->sub_category_name;
        $subCategory->category_id = $request->category_id;
        $subCategory->save();

        return redirect()->route('admin.sub_categories.index')->with('t-success', 'Sub Category updated successfully.');
    }

    public function status(int $id): JsonResponse {
        $data = SubCategory::findOrFail($id);
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
        $data = SubCategory::findOrFail($id);

        $data->delete();
        
        return response()->json([
            't-success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }
}
