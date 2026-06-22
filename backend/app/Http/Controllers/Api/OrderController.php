<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Create a new order.
     * Validates stock, prevents self-purchase, and calculates total correctly.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $user    = $request->user();
        $product = Product::findOrFail($request->product_id);

        // Prevent buying own product
        if ($product->seller_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك شراء منتجك الخاص.',
            ], 403);
        }

        // Check product is active
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'هذا المنتج غير متاح حالياً.',
            ], 422);
        }

        // Check sufficient stock
        $quantity = $request->quantity;
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "الكمية المطلوبة ({$quantity}) تتجاوز المخزون المتاح ({$product->stock}).",
            ], 422);
        }

        // Create the order with correct total (price × quantity)
        $order = Order::create([
            'buyer_id'         => $user->id,
            'seller_id'        => $product->seller_id,
            'product_id'       => $product->id,
            'order_number'     => 'ORD-' . strtoupper(Str::random(10)),
            'total_amount'     => $product->price * $quantity,
            'quantity'         => $quantity,
            'status'           => 'pending',
            'payment_status'   => 'pending',
            'payment_method'   => $request->payment_method,
            'notes'            => $request->notes,
            'shipping_address' => $request->shipping_address,
        ]);

        // Decrement stock after order is placed
        $product->decrement('stock', $quantity);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلبك بنجاح!',
            'data'    => new OrderResource($order->load(['product', 'seller'])),
        ], 201);
    }
}
