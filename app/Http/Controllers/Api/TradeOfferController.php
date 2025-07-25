<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\TradeOffer;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TradeOfferController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'offered_items' => 'required|array',
            'offered_items.*' => 'required|integer|exists:products,id',
            'requested_items' => 'required|array',
            'requested_items.*' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0',
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
                    'product_id' => $item,
                    'type' => 'offered',
                    'quantity' => $request->quantity,
                ]);
            }

            foreach ($request->requested_items as $item) {
                $offer->items()->create([
                    'product_id' => $item,
                    'type' => 'requested',
                    'quantity' => $request->quantity,
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
            ->with(['items.product:id,product_name,product_price,description', 'items.product.images', 'attachments', 'sender:id,first_name,last_name', 'sender.shopInfo:id,user_id,shop_name,shop_image', 'sender.shopInfo.address', 'receiver:id,first_name,last_name', 'receiver.shopInfo:id,user_id,shop_name,shop_image', 'receiver.shopInfo.address']);

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
            return $this->error([], 'Unauthorized action', 403);
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

        $previousCount = TradeOffer::where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })->where('created_at', '<', Carbon::today())->count();

        $tradeCount = $tradeCount ?: 0; // Ensure trade count is at least 0
        $pendingCount = $pendingCount ?: 0; // Ensure pending count is at least 0

        $tradeCount = [
            'trade_count' => $tradeCount,
            'pending_count' => $pendingCount,
            'cancelled_count' => $cancelledCount,
            'sent_count' => $sentCount,
            'previous_count' => $previousCount,
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

        // Validate the counter offer data
        $validator = Validator::make($request->all(), [
            'offered_items' => 'required|array',
            'offered_items.*' => 'required|integer|exists:products,id',
            'requested_items' => 'required|array',
            'requested_items.*' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            // Create a new trade offer for the counter
            $counterOffer = TradeOffer::create([
                'sender_id' => $user->id,
                'receiver_id' => $offer->sender_id,
                'inquiry' => Str::random(5),
                'message' => $request->message,
                'status' => 'pending',
            ]);

            foreach ($request->offered_items as $item) {
                $counterOffer->items()->create([
                    'product_id' => $item,
                    'type' => 'offered',
                    'quantity' => $request->quantity,
                ]);
            }

            foreach ($request->requested_items as $item) {
                $counterOffer->items()->create([
                    'product_id' => $item,
                    'type' => 'requested',
                    'quantity' => $request->quantity,
                ]);
            }
            DB::commit();
            $counterOffer->load('items');
            return $this->success($counterOffer, 'Trade counter offer sent successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to send trade counter offer: ' . $e->getMessage(), 500);
        }
    }
}
