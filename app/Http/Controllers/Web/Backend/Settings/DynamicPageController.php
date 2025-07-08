<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Models\DynamicPage;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class DynamicPageController extends Controller {
    /**
     * Display a listing of the dynamic pages.
     *
     * @param Request $request
     * @return View|JsonResponse
     * @throws Exception
     */
    public function index(Request $request): View | JsonResponse {
        if ($request->ajax()) {
            $data = DynamicPage::latest()->get();
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('page_title', 'LIKE', "%$searchTerm%");
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('page_content', function ($data) {
                    $page_content       = $data->page_content;
                    $short_page_content = strlen($page_content) > 100 ? substr($page_content, 0, 100) . '...' : $page_content;
                    return '<p>' . $short_page_content . '</p>';
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
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="' . route('dynamic_page.edit', ['id' => $data->id]) . '" type="button" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div>';
                })
                ->rawColumns(['page_content', 'status', 'action'])
                ->make();
        }
        return view('backend.layouts.settings.dynamic_page.index');
    }

    /**
     * Show the form for creating a new dynamic page.
     *
     * @return View|RedirectResponse
     */
    public function create(): View | RedirectResponse {
        if (User::find(auth()->user()->id)) {
            return view('backend.layouts.settings.dynamic_page.create');
        }
        return redirect()->route('dynamic_page.index');
    }

    /**
     * Store a newly created dynamic page in the database.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse {
        try {
            if (User::find(auth()->user()->id)) {
                $validator = Validator::make($request->all(), [
                    'page_title'   => 'required|string',
                    'sub_title'    => 'nullable|string',
                    'page_content' => 'required|string',
                    'page_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                if($request->hasFile('page_image')) {
                    $image                        = $request->file('page_image');
                    $imageName                    = uploadImage($image, 'dynamic_pages');
                }else {
                    $imageName = null; // Set a default value if no image is uploaded
                }

                $data               = new DynamicPage();
                $data->page_title   = $request->page_title;
                $data->sub_title = $request->sub_title;
                $data->page_slug    = Str::slug($request->page_title);
                $data->page_content = $request->page_content;
                $data->page_image = $imageName;
                $data->save();
            }
            return redirect()->route('dynamic_page.index')->with('t-success', 'Dynamic Page created successfully.');
        } catch (Exception) {
            return redirect()->route('dynamic_page.index')->with('t-error', 'Dynamic Page failed created.');
        }
    }

    /**
     * Show the form for editing the specified dynamic page.
     *
     * @param int $id
     * @return View|RedirectResponse
     */
    public function edit(int $id): View | RedirectResponse {
        if (User::find(auth()->user()->id)) {
            $data = DynamicPage::find($id);
            return view('backend.layouts.settings.dynamic_page.edit', compact('data'));
        }
        return redirect()->route('dynamic_page.index');
    }

    /**
     * Update the specified dynamic page in the database.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse {
        try {
            if (User::find(auth()->user()->id)) {
                $validator = Validator::make($request->all(), [
                   'page_title'   => 'required|string',
                    'sub_title'    => 'nullable|string',
                    'page_content' => 'required|string',
                    'page_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                $data = DynamicPage::findOrFail($id);
                if ($request->hasFile('page_image')) {
                    // Delete the old image if it exists
                    if (file_exists(public_path($data->page_image))) {
                        unlink(public_path($data->page_image));
                    }
                    $image = $request->file('page_image');
                    $imageName = uploadImage($image, 'dynamic_pages');
                } else {
                    // If no new image is uploaded, keep the old image
                    $imageName = $data->page_image;
                }
                $data->update([
                    'page_title'   => $request->page_title,
                    'page_slug'    => Str::slug($request->page_title),
                    'page_content' => $request->page_content,
                    'page_image' => $imageName,
                    'sub_title' => $request->sub_title,
                ]);

                return redirect()->route('dynamic_page.index')->with('t-success', 'Dynamic Page Updated Successfully.');
            }
        } catch (Exception) {
            return redirect()->route('dynamic_page.index')->with('t-error', 'Dynamic Page failed to update');
        }
        return redirect()->route('dynamic_page.index');
    }

    /**
     * Change the status of the specified dynamic page.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function status(int $id): JsonResponse {
        $data = DynamicPage::findOrFail($id);
        if ($data->status == 'active') {
            $data->status = 'inactive';
            $data->save();

            return response()->json([
                'success' => false,
                'message' => 'Unpublished Successfully.',
                'data'    => $data,
            ]);
        } else {
            $data->status = 'active';
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Published Successfully.',
                'data'    => $data,
            ]);
        }
    }

    /**
     * Remove the specified dynamic page from the database.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse {
        $page = DynamicPage::findOrFail($id);
        $page->delete();
        return response()->json([
            't-success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }
}
