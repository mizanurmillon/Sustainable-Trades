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
                ->addColumn('image', function ($data) {
                    $url = asset($data->image);
                    if (empty($data->image)) {
                        $url = asset('backend/images/placeholder/image_placeholder.png');
                    }
                    return '<img src="' . $url . '" class="img-fluid">';
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
                              <a href="' . route('admin.subscription.edit', ['id' => $data->id]) . '" class="text-white btn btn-primary" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="text-white btn btn-danger" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div></div>';
                })
                ->rawColumns(['description', 'action', 'price', 'image'])
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:yearly,monthly',
            'type' => 'required|in:basic,pro',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
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

            if ($request->hasFile('image')) {
                $image    = $request->image;
                $imageName = uploadImage($image, 'subscription/plan');
            }

            // 3. Store locally
            $plan = SubscriptionPlan::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'interval' => $request->interval,
                'membership_type' => $request->type,
                'paypal_plan_id' => $plan['id'],
                'product_id' => $product['id'],
                'image' => isset($imageName) ? $imageName : null
            ]);

            if ($request->subscription) {
                foreach ($request->subscription as $featureData) {
                    if (isset($featureData['benefit_icon']) && $featureData['benefit_icon']) {
                        $feature_image    = $featureData['benefit_icon'];
                        $FeatureImageName = uploadImage($feature_image, 'subscription/features');
                    } else {
                        $FeatureImageName = null;
                    }

                    SubscriptionBenefit::create([
                        'subscription_plan_id' => $plan['id'],
                        'benefit_name'         => $featureData['benefit_name'],
                        'benefit_description'  => $featureData['benefit_description'],
                        'benefit_icon'         => $FeatureImageName,
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

    public function edit($id)
    {
        // Logic to show form for editing a subscription plan
        $data = SubscriptionPlan::with('subscription_benefit')->findOrFail($id);
        return view('backend.layouts.subscription.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        // Logic to store a new subscription plan
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:yearly,monthly',
            'type' => 'required|in:basic,pro',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'subscription.*.benefit_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $paypal = new PayPalSubscriptionService();
            $plan = SubscriptionPlan::findOrFail($id);

            // --- Update PayPal Product ---
            $paypal->updateProduct($plan->product_id, [
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // --- If price or interval changed, create new PayPal Plan ---
            if ($request->price != $plan->price || $request->interval != $plan->interval) {
                $interval = $request->interval === 'monthly' ? 'MONTH' : 'YEAR';

                $newPlan = $paypal->createPlan(
                    $plan->product_id,
                    $request->name,
                    $request->description,
                    $request->price,
                    $interval
                );

                $plan->paypal_plan_id = $newPlan['id'];
            }

            if ($request->hasFile('image')) {

                if (!empty($plan->image) && file_exists(public_path($plan->image))) {
                    @unlink(public_path($plan->image));
                }

                $image = $request->file('image');
                $imageName = uploadImage($image, 'subscription/plan');
            } else {

                $imageName = $plan->image;
            }

            $plan->name = $request->name;
            $plan->description = $request->description;
            $plan->price = $request->price;
            $plan->interval = $request->interval;
            $plan->membership_type = $request->type;
            $plan->image = $imageName;
            $plan->save();

            if ($request->subscription) {
                foreach ($request->subscription as $featureData) {

                    if (empty($featureData['benefit_name']) && empty($featureData['benefit_description'])) {
                        continue;
                    }


                    if (isset($featureData['benefit_icon']) && $featureData['benefit_icon']) {
                        $feature_image    = $featureData['benefit_icon'];
                        $FeatureImageName = uploadImage($feature_image, 'subscription/features');
                    } else {
                        $FeatureImageName = null;
                    }

                    SubscriptionBenefit::create([
                        'subscription_plan_id' => $plan->id,
                        'benefit_name'         => $featureData['benefit_name'] ?? null,
                        'benefit_description'  => $featureData['benefit_description'] ?? null,
                        'benefit_icon'         => $FeatureImageName,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.subscription.index')->with('t-success', 'Subscription plan updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('t-error', 'Failed to update subscription plan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // Logic to delete a subscription plan
        $data = SubscriptionPlan::findOrFail($id);

        if ($data->image) {
            if (file_exists(public_path($data->image))) {
                unlink(public_path($data->image));
            }
        }

        $data->delete();

        return response()->json([
            't-success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }

    public function deleteBenefit($id)
    {
        $benefit = SubscriptionBenefit::findOrFail($id);

        if ($benefit->benefit_icon) {
            if (file_exists(public_path($benefit->benefit_icon))) {
                unlink(public_path($benefit->benefit_icon));
            }
        }


        $benefit->delete();
        return redirect()->back()->with('t-error', 'Benefit deleted successfully.');
    }

    public function editBenefit($id)
    {
        $data = SubscriptionBenefit::findOrFail($id);
        return view('backend.layouts.subscription.edit-benefit', compact('data'));
    }

    public function updateBenefit(Request $request, $id)
    {
        $request->validate([
            'benefit_name' => 'required|string|max:255',
            'benefit_description' => 'nullable|string',
            'benefit_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $benefit = SubscriptionBenefit::findOrFail($id);
        $benefit->benefit_name = $request->benefit_name;
        $benefit->benefit_description = $request->benefit_description;
        if ($request->benefit_icon) {
            if ($benefit->benefit_icon) {
                if (file_exists(public_path($benefit->benefit_icon))) {
                    unlink(public_path($benefit->benefit_icon));
                }
            }
            $feature_image    = $request->benefit_icon;
            $FeatureImageName = uploadImage($feature_image, 'subscription/features');
            $benefit->benefit_icon = $FeatureImageName;
        } else {
            $benefit->benefit_icon = $benefit->benefit_icon;
        }
        $benefit->save();
        return redirect()->route('admin.subscription.edit', $benefit->subscription_plan_id)->with('t-success', 'Benefit updated successfully.');
    }
}
