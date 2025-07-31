<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionBenefit;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Service\PayPalSubscriptionService;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = SubscriptionPlan::latest()->get();
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('name', 'LIKE', "%$searchTerm%");
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('description', function ($data) {
                    $description       = $data->description;
                    $short_description = strlen($description) > 60 ? substr($description, 0, 60) . '...' : $description;
                    return '<p>' . $short_description . '</p>';
                })
                ->addColumn('price', function ($data) {
                    return '$' . number_format($data->price, 2);
                })
                // ->addColumn('status', function ($data) {
                //     $status = ' <div class="form-check form-switch">';
                //     $status .= ' <input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status"';
                //     if ($data->status == "active") {
                //         $status .= "checked";
                //     }
                //     $status .= '><label for="customSwitch' . $data->id . '" class="form-check-label" for="customSwitch"></label></div>';

                //     return $status;
                // })
                ->addColumn('action', function ($data) {
                    return '<div class="text-center"><div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="' . route('admin.faqs.edit', ['id' => $data->id]) . '" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div></div>';
                })
                ->rawColumns(['description', 'action','price'])
                ->make(true);
        }
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
            'name' => 'required|string|max:255|unique:subscription_plans',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:yearly,monthly',
            'type' => 'required|in:basic,pro',
            'subscription.*.benefit_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // dd($request->all());

        try {
            DB::beginTransaction();
            
            $paypal = new PayPalSubscriptionService();

            // 1. Create product
            $product = $paypal->createProduct($request->name, $request->description);

             $interval = $request->interval === 'monthly' ? 'MONTH' : 'YEAR';

            // 2. Create plan
            $plan = $paypal->createPlan($product['id'], $request->name, $request->description, $request->price, $interval);

            // 3. Store locally
           $plan = SubscriptionPlan::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'interval' => $request->interval,
                'membership_type'=> $request->type,
                'paypal_plan_id' => $plan['id'],
                'product_id' => $product['id'],
            ]);

            if ($request->subscription) {
                foreach ($request->subscription as $featureData) {
                    if ($featureData['benefit_icon']) {
                        $feature_image    = $featureData['benefit_icon'];
                        $FeatureImageName = uploadImage($feature_image, 'subscription/features');
                    } else {
                        $FeatureImageName = null;
                    }
                    $benefit = SubscriptionBenefit::create([
                        'subscription_plan_id'=> $plan['id'],
                        'benefit_name'       => $featureData['benefit_name'],
                        'benefit_description' => $featureData['benefit_description'],
                        'benefit_icon'       => $FeatureImageName,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.subscription.index')->with('t-success', 'Subscription plan created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('t-error', 'Failed to create subscription plan: ' . $e->getMessage());
        }
    }
}
