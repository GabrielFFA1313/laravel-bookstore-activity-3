<?php

namespace App\Http\Controllers\Customer;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusUpdatedNotification;

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
        $this->authorize('view', $order);

        $order->load(['orderItems.book', 'user']);

        return view('orders.show', compact('order'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Order::class);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $total            = 0;
        $adjustedCart     = [];
        $adjustmentMessages = [];

        foreach ($cart as $key => $item) {
            $book = Book::find($item['book_id']);

            if (!$book || $book->stock_quantity === 0) {
                $adjustmentMessages[] = "{$book->title} is out of stock and was removed from your order.";
                continue;
            }

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

        $order = Order::create([
            'user_id'      => auth()->id(),
            'total_amount' => $total,
            'status'       => 'pending',
        ]);

        foreach ($adjustedCart as $item) {
            $book = Book::find($item['book_id']);

            $order->orderItems()->create([
                'book_id'    => $item['book_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['price'],
            ]);

            $book->decrement('stock_quantity', $item['quantity']);
        }

        session()->forget('cart');

        auth()->user()->notify(new OrderPlacedNotification($order));

        \App\Models\User::where('role', 'admin')->get()
            ->each(fn($admin) => $admin->notify(new \App\Notifications\NewOrderAdminNotification($order)));

        if (!empty($adjustmentMessages)) {
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order placed successfully!')
                ->with('warnings', $adjustmentMessages);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully!');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        if ($validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
            foreach ($order->orderItems as $item) {
                $item->book->increment('stock_quantity', $item->quantity);
            }
        }

        $order->update($validated);

        $order->user->notify(new OrderStatusUpdatedNotification($order));

        return back()->with('success', 'Order status updated successfully!');
    }
}