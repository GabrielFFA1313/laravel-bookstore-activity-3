@extends('layouts.app')

@section('title', auth()->user()->isAdmin() ? 'All Orders - PageTurner' : 'My Orders - PageTurner')

@section('content')
    <h1 class="text-3xl font-bold mb-6">{{ auth()->user()->isAdmin() ? 'All Orders' : 'My Orders' }}</h1>

    {{-- Filter by status (Admin only) --}}
    @if(auth()->user()->isAdmin())
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form action="{{ route('orders.index') }}" method="GET" class="flex gap-4">
                <select name="status" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Filter
                </button>
            </form>
        </div>
    @endif

    @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-semibold text-lg">Order #{{ $order->id }}</h3>
                    @if(auth()->user()->isAdmin())
                        <p class="text-gray-600 text-sm">Customer: {{ $order->user->name }} ({{ $order->user->email }})</p>
                    @endif
                    <p class="text-gray-600 text-sm">Placed on {{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                        @elseif($order->status === 'shipped') bg-indigo-100 text-indigo-800
                        @elseif($order->status === 'delivered') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                    <p class="text-xl font-bold text-indigo-600 mt-2">${{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="border-t pt-4">
                @foreach($order->orderItems as $item)
                    <div class="flex items-center py-2">
                        <div class="flex-1">
                            <p class="font-medium">{{ $item->book->title }}</p>
                            <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-semibold">${{ number_format($item->subtotal, 2) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex justify-between items-center">
                <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                    View Details →
                </a>

                {{-- Admin: Update Status Form --}}
        @if(auth()->user()->isAdmin())
             <div class="bg-white-50 rounded-lg p-4 mb-6">
        <div class="flex gap-4 justify-end items-center">
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
            </div>
        </div>
    @empty
        <x-alert type="info">
            {{ auth()->user()->isAdmin() ? 'No orders found.' : 'You haven\'t placed any orders yet.' }}
            @if(!auth()->user()->isAdmin())
                <a href="{{ route('books.index') }}" class="text-indigo-600 hover:underline">Start shopping!</a>
            @endif
        </x-alert>
    @endforelse

    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endsection