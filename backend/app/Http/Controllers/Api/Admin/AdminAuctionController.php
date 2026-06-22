<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuctionResource;
use App\Models\Auction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuctionController extends Controller
{
    private const ALLOWED_SORTS = ['created_at', 'start_time', 'end_time', 'current_price', 'status'];

    /**
     * Get all auctions with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Auction::with(['product', 'seller:id,first_name,last_name', 'winner:id,first_name,last_name'])
            ->withCount('bids');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', (int) $request->seller_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $sortBy    = in_array($request->sort_by, self::ALLOWED_SORTS) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        $perPage  = min((int) $request->get('per_page', 20), 100);
        $auctions = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => AuctionResource::collection($auctions)->response()->getData(true),
        ]);
    }

    /**
     * Get pending auctions (paginated).
     */
    public function pending(Request $request): JsonResponse
    {
        $perPage  = min((int) $request->get('per_page', 20), 100);
        $auctions = Auction::where('status', 'pending')
            ->with(['product', 'seller:id,first_name,last_name'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => AuctionResource::collection($auctions)->response()->getData(true),
        ]);
    }

    /**
     * Approve an auction.
     */
    public function approve(int $id): JsonResponse
    {
        $auction = Auction::findOrFail($id);

        if ($auction->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'هذا المزاد ليس في حالة انتظار.'], 422);
        }

        $auction->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'تمت الموافقة على المزاد بنجاح!',
            'data'    => new AuctionResource($auction),
        ]);
    }

    /**
     * Reject an auction.
     */
    public function reject(int $id): JsonResponse
    {
        $auction = Auction::findOrFail($id);

        if ($auction->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'هذا المزاد ليس في حالة انتظار.'], 422);
        }

        $auction->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض المزاد.',
            'data'    => new AuctionResource($auction),
        ]);
    }

    /**
     * Update an auction (Admin only).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $auction = Auction::findOrFail($id);

        $validated = $request->validate([
            'starting_price' => 'sometimes|numeric|min:0',
            'min_bid_increment' => 'sometimes|numeric|min:0',
            'start_time'     => 'sometimes|date',
            'end_time'       => 'sometimes|date|after:start_time',
            'status'         => 'sometimes|in:pending,active,ended,cancelled',
        ]);

        $auction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المزاد بنجاح!',
            'data'    => new AuctionResource($auction->load(['product', 'seller'])),
        ]);
    }

    /**
     * Delete an auction (Admin only — pending/cancelled only).
     */
    public function destroy(int $id): JsonResponse
    {
        $auction = Auction::findOrFail($id);

        if (in_array($auction->status, ['active', 'ended'])) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المزادات النشطة أو المنتهية.',
            ], 422);
        }

        $auction->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف المزاد بنجاح!']);
    }

    /**
     * Manually end an active auction.
     */
    public function end(int $id): JsonResponse
    {
        $auction = Auction::findOrFail($id);

        if ($auction->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'يمكن إنهاء المزادات النشطة فقط.'], 422);
        }

        $auction->update(['status' => 'ended']);

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء المزاد بنجاح!',
            'data'    => new AuctionResource($auction),
        ]);
    }
}
