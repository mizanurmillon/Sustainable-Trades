<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Enum\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\SpotlightApplication;
use App\Notifications\UserNotification;

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
        $application = SpotlightApplication::findOrFail($id);
        $application->status = 'approved';
        $application->save();

        $application->user->notify(new UserNotification(
            subject: 'Spotlight application approved',
            message: 'Your spotlight application has been approved.',
            channels: ['database'],
            type: NotificationType::SUCCESS,
        ));

        return response()->json([
            'success' => true,
            'message' => 'Spotlight application approved successfully.'
        ]);
    }

    public function pending(Request $request, $id)
    {
        $application = SpotlightApplication::findOrFail($id);
        $application->status = 'pending';
        $application->save();

        $application->user->notify(new UserNotification(
            subject: 'Spotlight application save for later',
            message: 'Your spotlight application has been save for later.',
            channels: ['database'],
            type: NotificationType::INFO,
        ));

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

        $data->user->notify(new UserNotification(
            subject: 'Spotlight application deleted',
            message: 'Your spotlight application has been deleted.',
            channels: ['database'],
            type: NotificationType::INFO,
        ));
        
        return response()->json([
            'success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }

}
