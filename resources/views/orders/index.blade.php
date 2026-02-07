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

                {{-- Admin: Update Status --}}
                @if(auth()->user()->isAdmin())
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="flex gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="border-gray-300 rounded-md text-sm">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-1 rounded text-sm hover:bg-indigo-700">
                            Update
                        </button>
                    </form>
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