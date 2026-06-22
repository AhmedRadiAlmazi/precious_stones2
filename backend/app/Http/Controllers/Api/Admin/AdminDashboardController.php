<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    /**
     * Get dashboard statistics with caching.
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            $stats = Cache::remember('admin_dashboard_stats', 300, function () {
                return [
                    'total_users'      => User::count(),
                    'total_buyers'     => User::where('account_type', 'buyer')->count(),
                    'total_sellers'    => User::where('account_type', 'seller')->where('is_approved', true)->count(),
                    'pending_sellers'  => User::where('account_type', 'seller')->where('is_approved', false)->count(),
                    'total_products'   => Product::count(),
                    'active_products'  => Product::where('is_active', true)->count(),
                    'total_auctions'   => Auction::count(),
                    'active_auctions'  => Auction::active()->count(),
                    'pending_auctions' => Auction::where('status', 'pending')->count(),
                    'total_bids'       => Bid::count(),
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل الإحصائيات.',
            ], 500);
        }
    }
}
