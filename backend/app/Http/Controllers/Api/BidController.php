<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\Request;

class BidController extends Controller
{
    /**
     * Place a bid on an auction
     */
    public function store(Request $request)
    {
        $request->validate([
            'auction_id' => 'required|exists:auctions,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $auction = Auction::with('bids')->findOrFail($request->auction_id);

        // Check if auction is active
        if (!$auction->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا المزاد غير نشط حالياً.',
            ], 422);
        }

        // Check if user is the seller
        if ($auction->seller_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك المزايدة على مزادك الخاص.',
            ], 403);
        }

        // Check minimum bid amount
        $minBid = $auction->current_price + $auction->bid_increment;
        if ($request->amount < $minBid) {
            return response()->json([
                'success' => false,
                'message' => "الحد الأدنى للمزايدة هو {$minBid} ر.س",
            ], 422);
        }

        // Create the bid
        $bid = Bid::create([
            'auction_id' => $auction->id,
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'is_winning' => true,
        ]);

        // Update previous winning bid
        Bid::where('auction_id', $auction->id)
            ->where('id', '!=', $bid->id)
            ->update(['is_winning' => false]);

        // Update auction current price and total bids
        $auction->update([
            'current_price' => $request->amount,
            'total_bids' => $auction->total_bids + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم المزايدة بنجاح!',
            'data' => $bid->load('user'),
        ], 201);
    }

    /**
     * Get user's bids
     */
    public function myBids(Request $request)
    {
        $bids = Bid::where('user_id', $request->user()->id)
            ->with(['auction.product'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bids,
        ]);
    }

    /**
     * Get bids for a specific auction
     */
    public function auctionBids($auctionId)
    {
        $auction = Auction::findOrFail($auctionId);
        
        $bids = Bid::where('auction_id', $auctionId)
            ->with('user:id,first_name,last_name')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bids,
        ]);
    }
}
