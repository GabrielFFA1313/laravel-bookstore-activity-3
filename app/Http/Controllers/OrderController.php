<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Book;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // If admin, show all orders; if customer, show only their orders
        if (auth()->user()->isAdmin()) {
            $orders = Order::with(['user', 'orderItems.book'])
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

        // Calculate total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Create order
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => $total,
            'status' => 'pending',
        ]);

        // Create order items and update stock
        foreach ($cart as $item) {
            $book = Book::find($item['book_id']);
            
            // Check stock
            if ($book->stock_quantity < $item['quantity']) {
                $order->delete();
                return back()->with('error', "Not enough stock for {$book->title}.");
            }

            // Create order item
            $order->orderItems()->create([
                'book_id' => $item['book_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
            ]);

            // Update stock
            $book->decrement('stock_quantity', $item['quantity']);
        }

        // Clear cart
        session()->forget('cart');

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }

    public function updateStatus(Request $request, Order $order)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update($validated);

        return back()->with('success', 'Order status updated successfully!');
    }
}