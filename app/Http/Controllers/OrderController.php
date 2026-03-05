<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Book;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
{
    if (auth()->user()->isAdmin()) {
        $orders = Order::with(['user', 'orderItems.book'])
            ->when($request->status, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15);
    } else {
        $orders = Order::where('user_id', auth()->id())
            ->with('orderItems.book')
            ->latest()
            ->paginate(10);
    }

    return view('orders.index', compact('orders'));
}

    public function show(Order $order)
    {
        // Check if user owns this order or is admin
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->load(['orderItems.book', 'user']);
        
        return view('orders.show', compact('order'));
    }

public function store(Request $request)
{
    $cart = session()->get('cart', []);
    
    if (empty($cart)) {
        return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
    }

    // Calculate total with capped quantities
    $total = 0;
    $adjustedCart = [];
    $adjustmentMessages = [];

    foreach ($cart as $key => $item) {
        $book = Book::find($item['book_id']);
        
        if (!$book || $book->stock_quantity === 0) {
            $adjustmentMessages[] = "{$book->title} is out of stock and was removed from your order.";
            continue;
        }

        // Cap quantity at available stock
        $finalQuantity = min($item['quantity'], $book->stock_quantity);

        if ($finalQuantity < $item['quantity']) {
            $adjustmentMessages[] = "{$book->title} quantity adjusted to {$finalQuantity} (max available).";
        }

        $adjustedCart[$key] = array_merge($item, ['quantity' => $finalQuantity]);
        $total += $item['price'] * $finalQuantity;
    }

    if (empty($adjustedCart)) {
        return redirect()->route('cart.index')->with('error', 'No items could be ordered due to stock issues.');
    }

    // Create order
    $order = Order::create([
        'user_id' => auth()->id(),
        'total_amount' => $total,
        'status' => 'pending',
    ]);

    // Create order items and update stock
    foreach ($adjustedCart as $item) {
        $book = Book::find($item['book_id']);

        $order->orderItems()->create([
            'book_id' => $item['book_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
        ]);

        $book->decrement('stock_quantity', $item['quantity']);
    }

    // Update cart session with adjusted quantities
    session()->put('cart', $adjustedCart);
    session()->forget('cart');

    if (!empty($adjustmentMessages)) {
        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!')
            ->with('warnings', $adjustmentMessages);
    } else {
        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }
}

    public function updateStatus(Request $request, Order $order)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        // Restore stock if order is being cancelled
    if ($validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
        foreach ($order->orderItems as $item) {
            $item->book->increment('stock_quantity', $item->quantity);
        }
    }

        $order->update($validated);

        return back()->with('success', 'Order status updated successfully!');
    }
}