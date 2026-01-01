<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {

        $totalProMembers = Membership::where('membership_type', 'pro')->count();
        $totalBasicMembers = Membership::where('membership_type', 'basic')->count();
        $totalCustom = User::where('role', 'customer')->count();

        $totalListingRequests = Product::where('status', 'pending')->count();
        $totalActiveListings = Product::where('status', 'approved')->count();

        $totalRevenue = Membership::sum('price');

        return view('backend.layouts.index', compact('totalProMembers', 'totalBasicMembers', 'totalCustom', 'totalListingRequests', 'totalActiveListings', 'totalRevenue'));
    }
}
