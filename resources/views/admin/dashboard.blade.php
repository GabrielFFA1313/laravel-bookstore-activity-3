@extends('layouts.app')

@section('title', 'Admin Dashboard - PageTurner')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}. Here's what's happening.</p>
        </div>
        <span class="text-sm text-gray-400">{{ now()->format('l, F j Y') }}</span>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">

        {{-- Users --}}
        <a href="{{ route('admin.users.index') }}"
           class="bg-white rounded-xl shadow p-5 flex items-center gap-4 hover:shadow-md transition group">
            <div class="bg-indigo-100 text-indigo-600 rounded-lg p-3 group-hover:bg-indigo-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
                <p class="text-sm text-gray-500">Customers</p>
            </div>
        </a>

        {{-- Books --}}
        <a href="{{ route('books.index') }}"
           class="bg-white rounded-xl shadow p-5 flex items-center gap-4 hover:shadow-md transition group">
            <div class="bg-green-100 text-green-600 rounded-lg p-3 group-hover:bg-green-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalBooks) }}</p>
                <p class="text-sm text-gray-500">Books</p>
            </div>
        </a>

        {{-- Categories --}}
        <a href="{{ route('categories.index') }}"
           class="bg-white rounded-xl shadow p-5 flex items-center gap-4 hover:shadow-md transition group">
            <div class="bg-yellow-100 text-yellow-600 rounded-lg p-3 group-hover:bg-yellow-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCategories) }}</p>
                <p class="text-sm text-gray-500">Categories</p>
            </div>
        </a>

        {{-- Orders --}}
        <a href="{{ route('orders.index') }}"
           class="bg-white rounded-xl shadow p-5 flex items-center gap-4 hover:shadow-md transition group">
            <div class="bg-blue-100 text-blue-600 rounded-lg p-3 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalOrders) }}</p>
                <p class="text-sm text-gray-500">Orders</p>
            </div>
        </a>

        {{-- Revenue --}}
        <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
            <div class="bg-red-100 text-red-600 rounded-lg p-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($totalRevenue, 2) }}</p>
                <p class="text-sm text-gray-500">Revenue</p>
            </div>
        </div>
    </div>


    {{-- Recent Orders + Recent Reviews --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Orders --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                <a href="{{ route('orders.index') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    View All
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentOrders as $order)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex items-center gap-4">
                            <div class="h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($order->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $order->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-400">Order #{{ $order->id }} &middot; {{ $order->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-semibold text-gray-900">${{ number_format($order->total_amount, 2) }}</span>
                            @php
                                $badgeColors = [
                                    'pending'    => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'shipped'    => 'bg-purple-100 text-purple-800',
                                    'delivered'  => 'bg-green-100 text-green-800',
                                    'cancelled'  => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                            <a href="{{ route('orders.show', $order) }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">
                        No orders yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Reviews --}}
        <div class="bg-white rounded-xl shadow">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Recent Reviews</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentReviews as $review)
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-medium text-gray-900">{{ $review->user->name ?? 'Unknown' }}</p>
                            <div class="flex text-yellow-400 text-xs">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <span>&#9733;</span>
                                    @else
                                        <span class="text-gray-300">&#9733;</span>
                                    @endif
                                @endfor
                            </div>
                        </div>
                        <a href="{{ route('books.show', $review->book) }}"
                           class="text-xs text-indigo-600 hover:underline">
                            {{ $review->book->title ?? 'Unknown Book' }}
                        </a>
                        @if($review->comment)
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $review->comment }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $review->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">
                        No reviews yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

            {{-- Export Actions --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Reports & Exports</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.orders.export') }}"
                    class="flex items-center gap-2 bg-indigo-50 text-indigo-700 px-4 py-3 rounded-lg hover:bg-indigo-100 transition font-medium text-sm">
                     Export Orders
                </a>
                <a href="{{ route('admin.orders.revenue') }}"
                    class="flex items-center gap-2 bg-green-50 text-green-700 px-4 py-3 rounded-lg hover:bg-green-100 transition font-medium text-sm">
                     Revenue Report
                </a>
                <a href="{{ route('admin.books.import') }}"
                    class="flex items-center gap-2 bg-yellow-50 text-yellow-700 px-4 py-3 rounded-lg hover:bg-yellow-100 transition font-medium text-sm">
                     Import Books
                </a>
                <a href="{{ route('admin.books.export') }}"
                    class="flex items-center gap-2 bg-gray-50 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-100 transition font-medium text-sm">
                     Export Books
                </a>
                
                <a href="{{ route('admin.users.import') }}"
                    class="flex items-center gap-2 bg-purple-50 text-purple-700 px-4 py-3 rounded-lg hover:bg-purple-100 transition font-medium text-sm">
                     Import Users
                </a>
                <a href="{{ route('admin.users.export') }}"
                    class="flex items-center gap-2 bg-pink-50 text-pink-700 px-4 py-3 rounded-lg hover:bg-pink-100 transition font-medium text-sm">
                     Export Users
                </a>
                <a href="{{ route('admin.backup.index') }}"
                class="flex items-center gap-2 bg-red-50 text-red-700 px-4 py-3 rounded-lg hover:bg-red-100 transition font-medium text-sm">
                 Backup & Maintenance
                </a>
                 <a href="{{ route('admin.audit.index') }}"
                    class="flex items-center gap-2 bg-red-50 text-blue-700 px-4 py-3 rounded-lg hover:bg-blue-100 transition font-medium text-sm">
                    Audit
                </a>
            </div>
        </div>

</div>
@endsection