<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Create a new order (Request a product).
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1', 
            'notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'payment_method' => 'nullable|string', // validate payment_method
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        // Prevent buying own product
        if ($product->seller_id === $user->id) {
            return response()->json([
                'message' => 'لا يمكنك شراء منتجك الخاص.'
            ], 403);
        }

        // Create Order
        $order = Order::create([
            'buyer_id' => $user->id,
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'total_amount' => $product->price, // Assuming quantity 1 for now or multiply if logical
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => $request->payment_method ?? 'bank_transfer', // Handle payment method
            'notes' => $request->notes,
            'shipping_address' => $request->shipping_address ?? 'Not provided', 
        ]);

        return response()->json([
            'message' => 'تم إرسال طلبك بنجاح!',
            'order' => $order
        ], 201);
    }
}
