<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    // ─────────────────────────────────────────────
    // INDEX — List authenticated user's orders
    // GET /api/orders
    // ─────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $orders = Order::select([
                'id', 'user_id', 'status',
                'total_amount', 'created_at',
                'shipping_name', 'shipping_city',
            ])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(['data' => $orders]);
    }

    // ─────────────────────────────────────────────
    // SHOW — Single order detail with items
    // GET /api/orders/{order}
    // ─────────────────────────────────────────────

    public function show(Request $request, Order $order): JsonResponse
    {
        // Customers can only see their own orders
        if ($request->user()->id !== $order->user_id
            && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Load order items with book details
        $order->load('orderItems.book:id,title,isbn,price');

        return response()->json([
            'data' => [
                'id'           => $order->id,
                'status'       => $order->status,
                'total_amount' => $order->total_amount,
                'shipping'     => [
                    'name'        => $order->shipping_name,
                    'phone'       => $order->shipping_phone,
                    'address'     => $order->shipping_address,
                    'city'        => $order->shipping_city,
                    'province'    => $order->shipping_province,
                    'postal_code' => $order->shipping_postal_code,
                ],
                'items' => $order->orderItems->map(fn($item) => [
                    'book'       => $item->book,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal'   => $item->subtotal,
                ]),
                'created_at' => $order->created_at->toDateTimeString(),
            ]
        ]);
    }
}