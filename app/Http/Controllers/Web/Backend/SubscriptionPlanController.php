<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Service\PayPalSubscriptionService;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        // Logic to display subscription plans
        return view('backend.layouts.subscription.index');
    }

    public function create()
    {
        // Logic to show form for creating a new subscription plan
        return view('backend.layouts.subscription.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // Logic to store a new subscription plan
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:Month,Year',
        ]);

        // dd($request->all());

        try {
            $paypal = new PayPalSubscriptionService();

            // 1. Create product
            $product = $paypal->createProduct($request->name, $request->description);

            // 2. Create plan
            $plan = $paypal->createPlan($product['id'], $request->name, $request->description, $request->price, $request->interval);

            // 3. Store locally
            SubscriptionPlan::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'interval' => $request->interval,
                'paypal_plan_id' => $plan['id'],
                'product_id' => $product['id'],
            ]);

            return redirect()->route('admin.subscription.index')->with('t-success', 'Subscription plan created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Failed to create subscription plan: ' . $e->getMessage());
        }
    }

    
}
