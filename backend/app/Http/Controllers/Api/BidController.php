<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBidRequest;
use App\Http\Resources\BidResource;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BidController extends Controller
{
    /**
     * Place a bid on an auction.
     * Uses DB::transaction + lockForUpdate to prevent race conditions.
     */
    public function store(StoreBidRequest $request): JsonResponse
    {
        $bid = null;

        try {
            DB::transaction(function () use ($request, &$bid) {
                // Lock the auction row to prevent concurrent bids
                $auction = Auction::lockForUpdate()->findOrFail($request->auction_id);

                // Check if auction is active (re-check inside transaction)
                if (!$auction->isActive()) {
                    abort(422, 'هذا المزاد غير نشط حالياً.');
                }

                // Prevent seller from bidding on their own auction
                if ($auction->seller_id === $request->user()->id) {
                    abort(403, 'لا يمكنك المزايدة على مزادك الخاص.');
                }

                // Enforce minimum bid amount
                $minBid = $auction->current_price + $auction->bid_increment;
                if ($request->amount < $minBid) {
                    abort(422, "الحد الأدنى للمزايدة هو {$minBid} ر.س");
                }

                // Mark all previous bids as non-winning
                Bid::where('auction_id', $auction->id)->update(['is_winning' => false]);

                // Create the new winning bid
                $bid = Bid::create([
                    'auction_id' => $auction->id,
                    'user_id'    => $request->user()->id,
                    'amount'     => $request->amount,
                    'is_winning' => true,
                ]);

                // Anti-sniping mechanism: extend by 2 minutes if bid is placed in the last 60 seconds
                $secondsLeft = now()->diffInSeconds($auction->end_time, false);
                if ($secondsLeft > 0 && $secondsLeft <= 60) {
                    $auction->end_time = $auction->end_time->addSeconds(120);
                }

                // Update auction price and bid counter atomically
                $auction->increment('total_bids');
                $auction->current_price = $request->amount;
                $auction->save();
            });

            if ($bid) {
                $bid->load('user');
                event(new \App\Events\BidPlaced($bid, $bid->auction));
            }
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            Log::error('Bid placement failed', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'فشل تقديم المزايدة. يرجى المحاولة مجدداً.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم المزايدة بنجاح!',
            'data'    => new BidResource($bid->load('user')),
        ], 201);
    }

    /**
     * Get the authenticated user's bids.
     */
    public function myBids(Request $request): JsonResponse
    {
        $bids = Bid::where('user_id', $request->user()->id)
            ->with(['auction.product'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => BidResource::collection($bids)->response()->getData(true),
        ]);
    }

    /**
     * Get bids for a specific auction (public).
     */
    public function auctionBids(int $auctionId): JsonResponse
    {
        // Verify auction exists
        Auction::findOrFail($auctionId);

        $bids = Bid::where('auction_id', $auctionId)
            ->with('user:id,first_name,last_name')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => BidResource::collection($bids)->response()->getData(true),
        ]);
    }
}
