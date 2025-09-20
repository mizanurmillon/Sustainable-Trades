<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\HowItWork;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HowItWorkController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = HowItWork::latest()->get();
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

                    return '<img src="' . $url . '" class="img-fluid" style="width: 50px; height: auto;">';
                })
                ->addColumn('description', function ($data) {
                    $description       = $data->description;
                    $short_description = strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                    return '<p>' . $short_description . '</p>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="text-center"><div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="' . route('admin.how_it_works.edit', ['id' => $data->id]) . '" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                            </div></div>';
                })
                ->rawColumns(['image', 'action', 'description'])
                ->make(true);
        }
        return view('backend.layouts.how_it_works.index');
    }

    public function edit($id)
    {
        $data = HowItWork::findOrFail($id);
        return view('backend.layouts.how_it_works.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
         $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:51200', // 50MB max
        ]);

        $data = HowItWork::findOrFail($id);

        if($request->hasFile('image')) {
            if(file_exists(public_path($data->image))){
                unlink(public_path($data->image));
            }
            $image                        = $request->file('image');
            $imageName                    = uploadImage($image, 'how_it_works');
        }else{
            $imageName = $data->image;
        }

        $data->title = $request->title;
        $data->description = $request->description;
        $data->image = $imageName;

        $data->save();

        return redirect()->route('admin.how_it_works.index')->with('t-success', 'How it works updated successfully');
    }
}
