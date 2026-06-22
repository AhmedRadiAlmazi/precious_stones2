<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Get all orders with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['product.category', 'buyer:id,first_name,last_name', 'seller:id,first_name,last_name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $orders  = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => OrderResource::collection($orders)->response()->getData(true),
        ]);
    }

    /**
     * Update order status (Admin only).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الطلب بنجاح!',
            'data'    => new OrderResource($order->load(['product', 'buyer', 'seller'])),
        ]);
    }
}
