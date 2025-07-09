<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('images','shop.user')->where('status', 'pending')->latest()->get();
        $product = Product::whereNot('status','listing')->count();
        return view('backend.layouts.listing.index', compact('products','product'));
    }
}
