<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = $this->calculateTotal($cart);
        
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request, Book $book)
    {
        $quantity = $request->input('quantity', 1);
        
        // Check stock
        if ($book->stock_quantity < $quantity) {
            return back()->with('error', 'Not enough stock available.');
        }

        $cart = session()->get('cart', []);

        // If book already in cart, update quantity
        if (isset($cart[$book->id])) {
            $newQuantity = $cart[$book->id]['quantity'] + $quantity;
            
            if ($newQuantity > $book->stock_quantity) {
                return back()->with('error', 'Cannot add more than available stock.');
            }
            
            $cart[$book->id]['quantity'] = $newQuantity;
        } else {
            // Add new book to cart
            $cart[$book->id] = [
                'book_id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'price' => $book->price,
                'quantity' => $quantity,
                'cover_image' => $book->cover_image,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Book added to cart!');
    }

    public function update(Request $request, $bookId)
    {
        $quantity = $request->input('quantity', 1);
        $cart = session()->get('cart', []);

        if (isset($cart[$bookId])) {
            $book = Book::find($bookId);
            
            if ($quantity > $book->stock_quantity) {
                return back()->with('error', 'Not enough stock available.');
            }

            if ($quantity <= 0) {
                return $this->remove($bookId);
            }

            $cart[$bookId]['quantity'] = $quantity;
            session()->put('cart', $cart);

            return back()->with('success', 'Cart updated!');
        }

        return back()->with('error', 'Book not found in cart.');
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