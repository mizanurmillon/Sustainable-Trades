<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TutorialsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Tutorial::latest()->get();
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('name', 'LIKE', "%$searchTerm%");
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('video', function ($data) {
                    $url = asset($data->video);

                    return '<video width="220" height="80" controls>
                            <source src="' . $url . '" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>';
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
                              <a href="' . route('admin.tutorials.edit', ['id' => $data->id]) . '" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div></div>';
                })
                ->rawColumns(['video', 'action', 'status'])
                ->make(true);
        }
        return view('backend.layouts.tutorials.index');
    }

    public function create()
    {
        // Logic to show the form for creating a new tutorial
        return view('backend.layouts.tutorials.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'video' => 'required|file|mimes:mp4,mov,avi,flv|max:20480', // 20MB max
            'description' => 'required|string|max:1000',
            'type' => 'required|in:owner,buyer',
        ]);

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $fileName = uploadImage($file, 'tutorials');
        }

        Tutorial::create([
            'name' => $request->name,
            'video' => $fileName,
            'description' => $request->description,
            'type' => $request->type,
        ]);

        return redirect()->route('admin.tutorials.index')->with('t-success', 'Tutorial created successfully.');
    }

    public function edit($id)
    {
        // Logic to show the form for editing an existing tutorial
        $data = Tutorial::findOrFail($id);
        return view('backend.layouts.tutorials.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'video' => 'nullable|file|mimes:mp4,mov,avi,flv|max:20480', // 20MB max
            'description' => 'required|string|max:1000',
            'type' => 'required|in:owner,buyer',
        ]);

        $tutorial = Tutorial::findOrFail($id);

        if ($request->hasFile('video')) {
            // Delete the old video file if it exists
            if ($tutorial->video) {
                $oldVideoPath = public_path($tutorial->video);
                if (file_exists($oldVideoPath)) {
                    unlink($oldVideoPath);
                }
            }
            $file = $request->file('video');
            $fileName = uploadImage($file, 'tutorials');
        } else {
            // If no new video is uploaded, keep the old one
            $fileName = $tutorial->video;
        }

        $tutorial->name = $request->name;
        $tutorial->description = $request->description;
        $tutorial->type = $request->type;
        $tutorial->video = $fileName;
        $tutorial->save();

        return redirect()->route('admin.tutorials.index')->with('t-success', 'Tutorial updated successfully.');
    }

    public function status(int $id): JsonResponse
    {
        $data = Tutorial::findOrFail($id);
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
        $data = Tutorial::findOrFail($id);

        if (file_exists(public_path($data->video))) {
            unlink(public_path($data->video));
        }

        $data->delete();

        return response()->json([
            't-success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }
}
