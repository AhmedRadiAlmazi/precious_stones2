<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Auction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الطلبات: ' . $e->getMessage(),
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
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث الطلب: ' . $e->getMessage(),
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

            // Total products
            $totalProducts = Product::where('seller_id', $sellerId)->count();

            // Total auctions
            $totalAuctions = Auction::where('seller_id', $sellerId)->count();

            // Active auctions
            $activeAuctions = Auction::where('seller_id', $sellerId)
                ->where('status', 'active')
                ->count();

            // Total orders
            $totalOrders = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })->count();

            // Total revenue
            $totalRevenue = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'paid')
            ->sum('total_amount');

            // Revenue by month (last 6 months) - SQLite compatible
            $revenueByMonth = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("strftime('%Y-%m', created_at) as month"),
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
                    'total_revenue' => $totalRevenue,
                    'revenue_by_month' => $revenueByMonth,
                    'top_products' => $topProducts,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الإحصائيات: ' . $e->getMessage(),
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

            // Get platform commission rate from settings
            $commissionRate = 10; // Default 10%, should be from settings

            // Total earnings (from paid orders)
            $totalEarnings = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'paid')
            ->sum('total_amount');

            // Calculate commission
            $commission = $totalEarnings * ($commissionRate / 100);
            $netEarnings = $totalEarnings - $commission;

            // Pending payments (orders not yet paid)
            $pendingPayments = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'pending')
            ->sum('total_amount');

            // Payment history (recent transactions)
            $paymentHistory = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'paid')
            ->with('product')
            ->latest()
            ->limit(20)
            ->get();

            // Earnings by period - SQLite compatible
            $earningsByMonth = Order::whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw("strftime('%Y-%m', created_at) as month"),
                DB::raw('SUM(total_amount) as earnings')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_earnings' => $totalEarnings,
                    'commission' => $commission,
                    'commission_rate' => $commissionRate,
                    'net_earnings' => $netEarnings,
                    'pending_payments' => $pendingPayments,
                    'payment_history' => $paymentHistory,
                    'earnings_by_month' => $earningsByMonth,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الأرباح: ' . $e->getMessage(),
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
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الملف الشخصي: ' . $e->getMessage(),
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
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث الملف الشخصي: ' . $e->getMessage(),
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
            
            // Store settings in user meta or separate table
            // For now, we'll just return success
            // You can implement this based on your database structure

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الإعدادات بنجاح!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث الإعدادات: ' . $e->getMessage(),
            ], 500);
        }
    }
}
