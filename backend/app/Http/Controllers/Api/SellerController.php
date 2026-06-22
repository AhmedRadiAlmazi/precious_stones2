<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerController extends Controller
{
    /**
     * Get seller's orders
     */
    public function getOrders(Request $request)
    {
        try {
            $sellerId = $request->user()->id;

            // Get orders for products owned by this seller
            $orders = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->with(['product', 'buyer'])
            ->latest()
            ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            Log::error('Seller getOrders error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الطلبات.',
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            ]);

            $order = Order::findOrFail($id);

            // Verify seller owns the product
            if ($order->product->seller_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتحديث هذا الطلب',
                ], 403);
            }

            $order->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الطلب بنجاح!',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('Seller updateOrderStatus error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث الطلب.',
            ], 500);
        }
    }

    /**
     * Get seller statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $sellerId = $request->user()->id;

            // Total products and auctions counts
            $totalProducts = Product::where('seller_id', $sellerId)->count();
            $totalAuctions = Auction::where('seller_id', $sellerId)->count();
            $activeAuctions = Auction::where('seller_id', $sellerId)
                ->where('status', 'active')
                ->count();

            // Combined orders query to prevent N+1 / query duplication
            $orderSummary = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->selectRaw("
                COUNT(*) as total_orders,
                SUM(CASE WHEN payment_status = 'completed' THEN total_amount ELSE 0 END) as total_revenue
            ")
            ->first();

            $totalOrders = $orderSummary->total_orders ?? 0;
            $totalRevenue = $orderSummary->total_revenue ?? 0.0;

            // Determine date formatting function based on database driver for portability
            $driverName = DB::connection()->getDriverName();
            $monthField = $driverName === 'sqlite' 
                ? "strftime('%Y-%m', created_at)" 
                : "DATE_FORMAT(created_at, '%Y-%m')";

            // Revenue by month (last 6 months)
            $revenueByMonth = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("{$monthField} as month"),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

            // Top selling products
            $topProducts = Product::where('seller_id', $sellerId)
                ->withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_products' => $totalProducts,
                    'total_auctions' => $totalAuctions,
                    'active_auctions' => $activeAuctions,
                    'total_orders' => $totalOrders,
                    'total_revenue' => floatval($totalRevenue),
                    'revenue_by_month' => $revenueByMonth,
                    'top_products' => $topProducts,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Seller getStatistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الإحصائيات.',
            ], 500);
        }
    }

    /**
     * Get seller earnings
     */
    public function getEarnings(Request $request)
    {
        try {
            $sellerId = $request->user()->id;

            // Get platform commission rate from settings (default 10%)
            $commissionRate = 10;

            // Combined orders earnings query to prevent multiple queries
            $earningsSummary = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->selectRaw("
                SUM(CASE WHEN payment_status = 'completed' THEN total_amount ELSE 0 END) as total_earnings,
                SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_payments
            ")
            ->first();

            $totalEarnings = $earningsSummary->total_earnings ?? 0.0;
            $pendingPayments = $earningsSummary->pending_payments ?? 0.0;

            // Calculate commission
            $commission = $totalEarnings * ($commissionRate / 100);
            $netEarnings = $totalEarnings - $commission;

            // Payment history (recent transactions)
            $paymentHistory = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'completed')
            ->with('product')
            ->latest()
            ->limit(20)
            ->get();

            // Determine date formatting function based on database driver for portability
            $driverName = DB::connection()->getDriverName();
            $monthField = $driverName === 'sqlite' 
                ? "strftime('%Y-%m', created_at)" 
                : "DATE_FORMAT(created_at, '%Y-%m')";

            // Earnings by period
            $earningsByMonth = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw("{$monthField} as month"),
                DB::raw('SUM(total_amount) as earnings')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_earnings' => floatval($totalEarnings),
                    'commission' => floatval($commission),
                    'commission_rate' => $commissionRate,
                    'net_earnings' => floatval($netEarnings),
                    'pending_payments' => floatval($pendingPayments),
                    'payment_history' => $paymentHistory,
                    'earnings_by_month' => $earningsByMonth,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Seller getEarnings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الأرباح.',
            ], 500);
        }
    }

    /**
     * Get seller profile
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Seller getProfile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الملف الشخصي.',
            ], 500);
        }
    }

    /**
     * Update seller profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
            ]);

            $user = $request->user();
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الملف الشخصي بنجاح!',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Seller updateProfile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث الملف الشخصي.',
            ], 500);
        }
    }

    /**
     * Update seller settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'notifications_enabled' => 'sometimes|boolean',
                'email_notifications' => 'sometimes|boolean',
                'sms_notifications' => 'sometimes|boolean',
            ]);

            $user = $request->user();
            $user->update([
                'settings' => array_merge($user->settings ?? [], $validated)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الإعدادات بنجاح!',
                'data' => $user->settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Seller updateSettings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث الإعدادات.',
            ], 500);
        }
    }
}
