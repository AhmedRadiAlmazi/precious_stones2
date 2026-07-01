<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use App\Models\Auction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get authenticated user's notifications (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $notifications->map(fn($n) => $this->formatNotification($n)),
            'unread_count' => Notification::where('user_id', $request->user()->id)
                ->whereNull('read_at')->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get unread count only (lightweight polling endpoint).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        // Also get the latest unread notifications (for toast display)
        $latest = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($n) => $this->formatNotification($n));

        return response()->json([
            'success'      => true,
            'unread_count' => $count,
            'latest'       => $latest,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'تم تحديد جميع الإشعارات كمقروءة']);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Delete all notifications for authenticated user.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف جميع الإشعارات']);
    }

    /**
     * Save / update the user's push notification preferences.
     */
    public function savePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'preferences'  => 'required|array',
            'push_enabled' => 'boolean',
        ]);

        $user = $request->user();
        $user->notification_preferences = json_encode($request->preferences);
        if ($request->has('push_enabled')) {
            $user->push_notifications_enabled = $request->push_enabled;
        }
        $user->save();

        return response()->json(['success' => true, 'message' => 'تم حفظ التفضيلات']);
    }

    /**
     * [Internal] Dispatch an outbid notification when a new bid surpasses a previous bidder.
     * Called from BidController after a successful bid.
     *
     * @param  int  $auctionId
     * @param  int  $previousBidderId   The user who was previously winning
     * @param  float $newBidAmount
     * @param  string $stoneName
     */
    public static function dispatchOutbid(int $auctionId, int $previousBidderId, float $newBidAmount, string $stoneName): void
    {
        try {
            Notification::create([
                'user_id'   => $previousBidderId,
                'type'      => 'outbid',
                'title'     => 'تجاوزتك مزايدة جديدة!',
                'body'      => "تم تجاوز عرضك على حجر «{$stoneName}» بمبلغ " . number_format($newBidAmount, 0) . ' ر.س. بادر بتقديم عرض أعلى الآن!',
                'icon'      => 'fas fa-gavel',
                'color'     => '#ef4444',
                'action_url' => "/auction-details?id={$auctionId}",
                'meta'      => json_encode(['auction_id' => $auctionId, 'new_bid' => $newBidAmount]),
                'read_at'   => null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create outbid notification', ['error' => $e->getMessage()]);
        }
    }

    /**
     * [Internal] Dispatch match alerts for a newly listed product.
     * Checks users whose preference history overlaps with the product's category/attributes.
     *
     * @param  Product  $product
     */
    public static function dispatchMatchAlerts(Product $product): void
    {
        try {
            // Find users who have previously bid on or viewed items of the same category
            $interestedUserIds = DB::table('bids')
                ->join('auctions', 'bids.auction_id', '=', 'auctions.id')
                ->join('products', 'auctions.product_id', '=', 'products.id')
                ->where('products.category_id', $product->category_id)
                ->where('products.id', '!=', $product->id)
                ->distinct()
                ->pluck('bids.user_id');

            foreach ($interestedUserIds as $userId) {
                // Avoid flooding: check if user already has an unread match alert for same category
                $recentMatch = Notification::where('user_id', $userId)
                    ->where('type', 'match_alert')
                    ->whereNull('read_at')
                    ->where('created_at', '>=', now()->subHours(6))
                    ->exists();

                if ($recentMatch) continue;

                Notification::create([
                    'user_id'    => $userId,
                    'type'       => 'match_alert',
                    'title'      => 'حجر يناسب اهتماماتك!',
                    'body'       => "تم إضافة حجر «{$product->name}» يتطابق مع تفضيلاتك. استكشفه الآن!",
                    'icon'       => 'fas fa-gem',
                    'color'      => '#D4AF37',
                    'action_url' => "/shop?product={$product->id}",
                    'meta'       => json_encode(['product_id' => $product->id, 'category_id' => $product->category_id]),
                    'read_at'    => null,
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to dispatch match alerts', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Format a notification model into a clean array for the API response.
     */
    private function formatNotification(Notification $n): array
    {
        return [
            'id'         => $n->id,
            'type'       => $n->type,
            'title'      => $n->title,
            'body'       => $n->body,
            'icon'       => $n->icon ?? 'fas fa-bell',
            'color'      => $n->color ?? '#D4AF37',
            'action_url' => $n->action_url,
            'is_read'    => !is_null($n->read_at),
            'read_at'    => $n->read_at,
            'meta'       => $n->meta ? json_decode($n->meta, true) : null,
            'created_at' => $n->created_at->diffForHumans(),
            'created_at_raw' => $n->created_at->toIso8601String(),
        ];
    }
}
