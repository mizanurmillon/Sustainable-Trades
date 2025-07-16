<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\SpotlightApplication;
use Illuminate\Http\Request;

class SpotlightApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = SpotlightApplication::with('user');

        // Status filter (pending / approved)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Name filter (optional)
        if ($request->filled('name')) {
            $query->where('shop_name', 'like', '%' . $request->name . '%');
        }

        $applications = $query->latest()->get();

        $approved = SpotlightApplication::where('status', 'approved')->count();
        $pending = SpotlightApplication::where('status', 'pending')->count();

        return view('backend.layouts.spotlight.index', compact('applications','approved','pending'));
    }

    public function show($id)
    {
        $application = SpotlightApplication::findOrFail($id);
        return view('backend.layouts.spotlight.show', compact('application'));
    }

    public function approve(Request $request, $id)
    {
        $product = SpotlightApplication::findOrFail($id);
        $product->status = 'approved';
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Spotlight application approved successfully.'
        ]);
    }

    public function pending(Request $request, $id)
    {
        $product = SpotlightApplication::findOrFail($id);
        $product->status = 'pending';
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Spotlight application save for later.'
        ]);
    }

    public function destroy($id)
    {
        $data = SpotlightApplication::findOrFail($id);

        if(file_exists(public_path($data->image))){
            unlink(public_path($data->image));
        }

        $data->delete();
        
        return response()->json([
            'success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }

}
