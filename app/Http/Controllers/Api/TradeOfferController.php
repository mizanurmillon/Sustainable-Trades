<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TradeOffer;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TradeOfferController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'offered_items' => 'required|array',
            'offered_items.*.product_id' => 'required|integer|exists:products,id',
            'offered_items.*.quantity' => 'required|integer|min:1',
            'requested_items' => 'required|array',
            'requested_items.*.product_id' => 'required|integer|exists:products,id',
            'requested_items.*.quantity' => 'required|integer|min:1',
            'message' => 'nullable|string|max:500',
            'receiver_id' => 'required|integer|exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        try {
            DB::beginTransaction();
            $offer = TradeOffer::create([
                'sender_id' => $user->id,
                'receiver_id' => $request->receiver_id,
                'inquiry' =>  Str::random(5),
                'message' => $request->message,
            ]);

            foreach ($request->offered_items as $item) {
                $offer->items()->create([
                    'product_id' => $item['product_id'],
                    'type' => 'offered',
                    'quantity' => $item['quantity'],
                ]);
            }

            foreach ($request->requested_items as $item) {
                $offer->items()->create([
                    'product_id' => $item['product_id'],
                    'type' => 'requested',
                    'quantity' => $item['quantity'],
                ]);
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = uploadImage($file, 'trade_offers');
                    $offer->attachments()->create(['file_path' => $path]);
                }
            }
            DB::commit();
            $offer->load(['items', 'attachments']);
            return $this->success($offer, 'Trade offer created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to create trade offer: ' . $e->getMessage(), 500);
        }
    }

    public function getTradeOffers(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $query = TradeOffer::where(function ($q) use ($user) {
            $q->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })
            ->with(['items.product:id,shop_info_id,product_name,product_price,description','items.product.shop:id,user_id,shop_name', 'items.product.images', 'attachments', 'sender:id,first_name,last_name', 'sender.shopInfo:id,user_id,shop_name,shop_image', 'sender.shopInfo.address', 'receiver:id,first_name,last_name', 'receiver.shopInfo:id,user_id,shop_name,shop_image', 'receiver.shopInfo.address']);

        // Filter by Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->whereHas('items.product', function ($q) use ($searchTerm) {
                $q->where('product_name', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter only "sent" offers if requested
        if ($request->filled('sent') && $request->sent) {
            $query->where('sender_id', $user->id);
        }

        // Filter only "previous" offers (before today)
        if ($request->filled('previous') && $request->previous) {
            $query->where('created_at', '<', Carbon::today());
        }

        $offers = $query->get();

        return $this->success($offers, 'Trade offers retrieved successfully');
    }

    public function approveTradeOffer($id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $offer = TradeOffer::find($id);

        if (!$offer) {
            return $this->error([], 'Trade offer not found', 404);
        }

        if ($offer->status === 'accepted') {
            return $this->error([], 'Trade offer already accepted', 400);
        }

        if ($offer->receiver_id !== $user->id) {
            return $this->error([], 'This trade offer is your offer and you cannot approve it', 404);
        }

        // Logic to approve the trade offer
        // This could involve updating the status of the offer, notifying users, etc.
        $offer->status = 'accepted';
        $offer->save();

        // For now, we will just return a success message
        return $this->success($offer, 'Trade offer accepted successfully');
    }

    public function cancelTradeOffer($id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $offer = TradeOffer::find($id);

        if (!$offer) {
            return $this->error([], 'Trade offer not found', 404);
        }

        if ($offer->status === 'cancelled') {
            return $this->error([], 'Trade offer already cancelled', 400);
        }

        if ($offer->sender_id !== $user->id && $offer->receiver_id !== $user->id) {
            return $this->error([], 'Unauthorized action', 403);
        }

        // Logic to cancel the trade offer
        // This could involve updating the status of the offer, notifying users, etc.
        $offer->status = 'cancelled';
        $offer->save();

        return $this->success($offer, 'Trade offer cancelled successfully');
    }

    public function getTradeCount()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        // Total Trade Count
        $tradeCount = TradeOffer::where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })->count();

        // Pending Trade Count
        $pendingCount = TradeOffer::where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })->where('status', 'pending')->count();

        // Cancelled Trade Count
        $cancelledCount = TradeOffer::where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })->where('status', 'cancelled')->count();

        $sentCount = TradeOffer::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id);
        })->count();

        $acceptedCount = TradeOffer::where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })->where('status', 'accepted')->count();

        $tradeCount = $tradeCount ?: 0; // Ensure trade count is at least 0
        $pendingCount = $pendingCount ?: 0; // Ensure pending count is at least 0

        $tradeCount = [
            'trade_count' => $tradeCount,
            'pending_count' => $pendingCount,
            'cancelled_count' => $cancelledCount,
            'sent_count' => $sentCount,
            'accepted_count' => $acceptedCount,
        ];

        return $this->success($tradeCount, 'Trade count retrieved successfully', 200);
    }

    public function sendTradeCounterOffer(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $offer = TradeOffer::find($id);

        if (!$offer) {
            return $this->error([], 'Trade offer not found', 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'offered_items' => 'required|array',
            'offered_items.*.product_id' => 'required|integer|exists:products,id',
            'offered_items.*.quantity' => 'required|integer|min:1',
            'requested_items' => 'required|array',
            'requested_items.*.product_id' => 'required|integer|exists:products,id',
            'requested_items.*.quantity' => 'required|integer|min:1',
            'message' => 'nullable|string|max:500',
            'receiver_id' => 'required|integer|exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            $counterOffer = TradeOffer::create([
                'sender_id' => $user->id,
                'receiver_id' => $request->receiver_id,
                'parent_offer_id' => $offer->id,
                'inquiry' =>  Str::random(5),
                'message' => $request->message,
            ]);

            foreach ($request->offered_items as $item) {
                $offer->items()->create([
                    'product_id' => $item['product_id'],
                    'type' => 'offered',
                    'quantity' => $item['quantity'],
                ]);
            }

            foreach ($request->requested_items as $item) {
                $offer->items()->create([
                    'product_id' => $item['product_id'],
                    'type' => 'requested',
                    'quantity' => $item['quantity'],
                ]);
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = uploadImage($file, 'trade_offers');
                    $offer->attachments()->create(['file_path' => $path]);
                }
            }
           
            DB::commit();
            $counterOffer->load('items', 'attachments');
            return $this->success($counterOffer, 'Trade counter offer sent successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to send trade counter offer: ' . $e->getMessage(), 500);
        }
    }


    public function tradeShopProduct($id)
    {
        $query = Product::where('shop_info_id', $id)
            ->where('status', 'approved')
            ->whereNot('selling_option', 'For Sale')
            ->where('product_quantity', '>', 0)
            ->select('id', 'shop_info_id', 'product_name', 'product_price','product_quantity', 'selling_option');

        $data = $query->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No products found for this shop', 404);
        }

        return $this->success($data, 'Products retrieved successfully', 200);
    }
}
