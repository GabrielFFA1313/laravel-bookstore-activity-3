@extends('layouts.app')

@section('title', 'Shopping Cart - PageTurner')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Shopping Cart</h1>

    @if(session('success'))
        <x-alert type="success" class="mb-4">
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="error" class="mb-4">
            {{ session('error') }}
        </x-alert>
    @endif

    @if(count($cart) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Cart Items --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    @foreach($cart as $id => $item)
                        <div class="p-6 border-b last:border-b-0">
                            <div class="flex items-center gap-4">
                                {{-- Book Cover --}}
                                <div class="w-20 h-28 bg-gray-200 rounded flex-shrink-0">
                                    @if($item['cover_image'])
                                        <img src="{{ asset('storage/' . $item['cover_image']) }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover rounded">
                                    @else
                                        <svg class="w-full h-full text-gray-400 p-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    @endif
                                </div>

                                {{-- Book Info --}}
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg">{{ $item['title'] }}</h3>
                                    <p class="text-gray-600 text-sm">by {{ $item['author'] }}</p>
                                    <p class="text-indigo-600 font-semibold mt-1">${{ number_format($item['price'], 2) }}</p>
                                </div>

                                {{-- Quantity Controls --}}
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('cart.update', $id) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="w-16 border-gray-300 rounded text-center">
                                        <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                            Update
                                        </button>
                                    </form>
                                </div>

                                {{-- Subtotal & Remove --}}
                                <div class="text-right">
                                    <p class="font-bold text-lg">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                    <form action="{{ route('cart.remove', $id) }}" method="POST" class="mt-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Clear Cart Button --}}
                <form action="{{ route('cart.clear') }}" method="POST" class="mt-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure you want to clear the cart?')" class="text-red-600 hover:text-red-800">
                        Clear Cart
                    </button>
                </form>
            </div>

            {{-- Cart Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal ({{ count($cart) }} items)</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                    </div>

                    <div class="border-t pt-4 mb-6">
                        <div class="flex justify-between font-bold text-xl">
                            <span>Total</span>
                            <span class="text-indigo-600">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <form action="{{ route('orders.store') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
                            Proceed to Checkout
                        </button>
                    </form>

                    <a href="{{ route('books.index') }}" class="block text-center text-indigo-600 hover:text-indigo-800 mt-4">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="h-24 w-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Your cart is empty</h2>
            <p class="text-gray-600 mb-6">Start adding some books to your cart!</p>
            <a href="{{ route('books.index') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
                Browse Books
            </a>
        </div>
    @endif
@endsection