<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('images','shop.user')->whereNot('status', 'listing');

        // Status filter (pending / approved / denied)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Name filter (optional)
        if ($request->filled('name')) {
            $query->where('product_name', 'like', '%' . $request->name . '%');
        }

        $products = $query->latest()->get();


        $product = Product::whereNot('status','listing')->count();
        return view('backend.layouts.listing.index', compact('products','product'));
    }

    public function show($id)
    {
        $product = Product::with('images', 'shop.user','category','sub_category','metaTags')->findOrFail($id);
        $categories = Category::with('subcategories')->where('status', 'active')->get();
        return view('backend.layouts.listing.show', compact('product','categories'));
    }

    public function approve(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'approved';
        $product->save();

        $product->shop->user->notify(new UserNotification(
            subject: 'Product listing approved',
            message: 'Your product listing has been approved.',
            channels: ['database'],
            type: NotificationType::SUCCESS,
        ));

        return response()->json([
            'success' => true,
            'message' => 'Product listing approved successfully.'
        ]);
    }

    public function reject(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'rejected';
        $product->save();

        $product->shop->user->notify(new UserNotification(
            subject: 'Product listing rejected',
            message: 'Your product listing has been rejected.',
            channels: ['database'],
            type: NotificationType::DANGER,
        ));

        return response()->json([
            'success' => true,
            'message' => 'Product listing rejected successfully.'
        ]);
    }
}
