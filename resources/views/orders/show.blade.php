@extends('layouts.app')

@section('title', 'Order #' . $order->id . ' - PageTurner')

@section('content')
    <div class="mb-6">
        <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:text-indigo-800">
            ← Back to Orders
        </a>
    </div>


    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold">Order #{{ $order->id }}</h1>
                @if(auth()->user()->isAdmin())
                    <p class="text-gray-600 mt-1">Customer: {{ $order->user->name }}</p>
                    <p class="text-gray-600">Email: {{ $order->user->email }}</p>
                @endif
                <p class="text-gray-600 mt-1">Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-medium
                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                @elseif($order->status === 'shipped') bg-indigo-100 text-indigo-800
                @elseif($order->status === 'delivered') bg-green-100 text-green-800
                @else bg-red-100 text-red-800
                @endif">
                {{ ucfirst($order->status) }}
            </span>
        </div>
        {{-- Shipping Address --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold mb-3">Shipping Address</h3>
                @if($order->shipping_name)
                    <p class="font-medium text-gray-900">{{ $order->shipping_name }}</p>
                    <p class="text-sm text-gray-600">{{ $order->shipping_phone }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $order->shipping_address }}</p>
                    <p class="text-sm text-gray-600">{{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
                @else
                    <p class="text-sm text-gray-400">No address on record.</p>
                @endif
            </div>

        

       {{-- Admin: Update Status Form --}}
        @if(auth()->user()->isAdmin())
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold mb-3">Update Order Status</h3>
                <div class="flex gap-4">
                    {{-- Progress Status Button --}}
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        @php
                            $nextStatus = match($order->status) {
                                'pending' => 'processing',
                                'processing' => 'shipped',
                                'shipped' => 'delivered',
                                default => null
                            };
                            
                            $buttonText = match($nextStatus) {
                                'processing' => 'Mark as Processing',
                                'shipped' => 'Mark as Shipped',
                                'delivered' => 'Mark as Delivered',
                                default => 'Status Updated'
                            };
                        @endphp
                        
                        @if($nextStatus)
                            <input type="hidden" name="status" value="{{ $nextStatus }}">
                            <button 
                                type="submit" 
                                class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700"
                            >
                                {{ $buttonText }}
                            </button>
                        @else
                            <button 
                                type="button" 
                                class="bg-gray-400 text-white px-6 py-2 rounded cursor-not-allowed"
                                disabled
                            >
                                {{ $buttonText }}
                            </button>
                        @endif
                    </form>

                    {{-- Cancel Order Button --}}
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button 
                            type="submit" 
                            class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                            {{ in_array($order->status, ['delivered', 'cancelled']) ? 'disabled' : '' }}
                        >
                            Cancel Order
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Order Items --}}
        <div class="border-t border-b py-4 mb-4">
            <h2 class="font-semibold text-lg mb-4">Items</h2>
            @foreach($order->orderItems as $item)
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center flex-1">
                        <div class="h-16 w-16 bg-gray-200 rounded flex-shrink-0">
                            @if($item->book->cover_image)
                                <img src="{{ asset('storage/' . $item->book->cover_image) }}" alt="{{ $item->book->title }}" class="h-full w-full object-cover rounded">
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="font-medium">{{ $item->book->title }}</p>
                            <p class="text-sm text-gray-600">by {{ $item->book->author }}</p>
                            <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }} × ${{ number_format($item->unit_price, 2) }}</p>
                        </div>
                    </div>
                    <p class="font-semibold text-lg">${{ number_format($item->subtotal, 2) }}</p>
                </div>
            @endforeach
        </div>

        {{-- Order Summary --}}
        <div class="flex justify-end">
            <div class="w-64">
                <div class="flex justify-between py-2 text-lg font-bold border-t">
                    <span>Total:</span>
                    <span class="text-indigo-600">${{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection