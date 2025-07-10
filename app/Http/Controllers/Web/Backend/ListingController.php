<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('images','shop.user');

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

        return response()->json([
            'success' => true,
            'message' => 'Product listing rejected successfully.'
        ]);
    }
}
