<?php

namespace App\Http\Controllers\Customer;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
   public function index()
    {
        $cart      = session()->get('cart', []);
        $total     = $this->calculateTotal($cart);
        $addresses = auth()->user()->addresses()->get();

        return view('cart.index', compact('cart', 'total', 'addresses'));
    }

   public function update(Request $request, $bookId)
{
    $quantity = $request->input('quantity', 1);
    $cart = session()->get('cart', []);

    if (isset($cart[$bookId])) {
        $book = Book::find($bookId);

        if ($quantity <= 0) {
            return $this->remove($bookId);
        }

        // Cap at available stock instead of erroring
        $finalQuantity = min($quantity, $book->stock_quantity);
        $cart[$bookId]['quantity'] = $finalQuantity;
        session()->put('cart', $cart);

        $message = $finalQuantity < $quantity
            ? "Quantity adjusted to {$finalQuantity} (only {$book->stock_quantity} in stock)."
            : 'Cart updated!';

        return back()->with('success', $message);
    }

    return back()->with('error', 'Book not found in cart.');
}

public function add(Request $request, Book $book)
{
    $quantity = $request->input('quantity', 1);
    $cart = session()->get('cart', []);

    if (isset($cart[$book->id])) {
        $newQuantity = $cart[$book->id]['quantity'] + $quantity;
        // Cap instead of erroring
        $finalQuantity = min($newQuantity, $book->stock_quantity);
        $cart[$book->id]['quantity'] = $finalQuantity;
    } else {
        if ($book->stock_quantity === 0) {
            return back()->with('error', 'This book is out of stock.');
        }
        $finalQuantity = min($quantity, $book->stock_quantity);
        $cart[$book->id] = [
            'book_id'     => $book->id,
            'title'       => $book->title,
            'author'      => $book->author,
            'price'       => $book->price,
            'quantity'    => $finalQuantity,
            'cover_image' => $book->cover_image,
        ];
    }

    session()->put('cart', $cart);
    return back()->with('success', 'Book added to cart!');
}
    public function remove($bookId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$bookId])) {
            unset($cart[$bookId]);
            session()->put('cart', $cart);
            
            return back()->with('success', 'Book removed from cart.');
        }

        return back()->with('error', 'Book not found in cart.');
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Cart cleared!');
    }

    private function calculateTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getCartCount()
    {
        $cart = session()->get('cart', []);
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
}