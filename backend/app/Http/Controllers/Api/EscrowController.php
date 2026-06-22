<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EscrowController extends Controller
{
    /**
     * Pay for an order using the buyer's wallet balance (holds funds in escrow).
     */
    public function payWithWallet(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();
        $order = Order::findOrFail($orderId);

        if ($order->buyer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالدفع لهذا الطلب.',
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الطلب تم دفعه بالفعل أو ملغي.',
            ], 422);
        }

        if ($user->wallet_balance < $order->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'رصيد المحفظة غير كافٍ لإتمام عملية الشراء.',
            ], 400);
        }

        try {
            DB::transaction(function () use ($user, $order) {
                // Deduct balance from buyer
                $user->decrement('wallet_balance', $order->total_amount);

                // Update order status to paid (held in escrow)
                $order->update([
                    'status' => 'paid',
                    'payment_method' => 'wallet',
                    'payment_status' => 'pending', // pending release from escrow
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'تم الدفع بنجاح من المحفظة وحجز المبلغ في الضمان.',
                'data' => $order->fresh(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Escrow Payment Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'فشلت عملية الدفع. يرجى المحاولة لاحقاً.',
            ], 500);
        }
    }

    /**
     * Seller marks order as shipped and inputs tracking details.
     */
    public function shipOrder(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $order = Order::findOrFail($orderId);

        if ($order->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بشحن هذا الطلب.',
            ], 403);
        }

        if ($order->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن شحن طلب لم يتم دفع قيمته بعد.',
            ], 422);
        }

        $order->update([
            'status' => 'shipped',
            'tracking_number' => $request->tracking_number,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الشحن وإضافة رقم التتبع بنجاح.',
            'data' => $order,
        ]);
    }

    /**
     * Buyer or Admin confirms delivery of the gemstone.
     */
    public function deliverOrder(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();
        $order = Order::findOrFail($orderId);

        // Allow buyer or admin to confirm delivery
        if ($order->buyer_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتأكيد استلام هذا الطلب.',
            ], 403);
        }

        if ($order->status !== 'shipped') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تأكيد استلام طلب لم يتم شحنه بعد.',
            ], 422);
        }

        $order->update([
            'status' => 'delivered',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تأكيد الاستلام بنجاح وبدأت فترة الفحص والمعاينة.',
            'data' => $order,
        ]);
    }

    /**
     * Buyer releases the escrowed funds to the seller after successful inspection.
     */
    public function releaseFunds(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();
        $order = Order::findOrFail($orderId);

        if ($order->buyer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتحرير أموال هذا الطلب.',
            ], 403);
        }

        if ($order->status !== 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تحرير الأموال إلا بعد استلام المنتج وفحصه.',
            ], 422);
        }

        if ($order->payment_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'تم تحرير الأموال وتثبيتها مسبقاً لهذا الطلب.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($order) {
                // Find seller and credit their wallet balance
                $seller = User::findOrFail($order->seller_id);
                $seller->increment('wallet_balance', $order->total_amount);

                // Update payment status to completed
                $order->update([
                    'payment_status' => 'completed',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'تم فحص المنتج بنجاح وتحرير الأموال لحساب البائع.',
                'data' => $order->fresh(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Escrow Release Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'فشل تحرير الأموال. يرجى المحاولة لاحقاً.',
            ], 500);
        }
    }
}
