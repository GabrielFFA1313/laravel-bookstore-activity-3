@extends('layouts.app')

@section('title', 'My Dashboard - PageTurner')

@section('content')
<div class="space-y-8">

    {{-- Welcome Header --}}
    <div class="bg-indigo-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Welcome back, {{ $user->name }}!</h1>
                <p class="text-indigo-200 mt-1 text-sm">Here's a summary of your account activity.</p>
            </div>
            <div class="hidden sm:flex items-center gap-3">
                {{-- Email verification status --}}
                @if($user->hasVerifiedEmail())
                    <span class="flex items-center gap-1 bg-green-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Email Verified
                    </span>
                @else
                    <a href="{{ route('verification.notice') }}"
                       class="flex items-center gap-1 bg-yellow-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full hover:bg-yellow-400 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Unverified Email
                    </a>
                @endif

                {{-- 2FA status --}}
                @if($user->hasTwoFactorEnabled())
                    <span class="flex items-center gap-1 bg-green-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        2FA On
                    </span>
                @else
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-1 bg-indigo-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full hover:bg-indigo-400 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                        Enable 2FA
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Account Status (mobile) --}}
    <div class="flex flex-wrap gap-3 sm:hidden">
        @if($user->hasVerifiedEmail())
            <span class="flex items-center gap-1 bg-green-100 text-green-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                Email Verified
            </span>
        @else
            <a href="{{ route('verification.notice') }}"
               class="flex items-center gap-1 bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                Unverified Email
            </a>
        @endif
        @if($user->hasTwoFactorEnabled())
            <span class="flex items-center gap-1 bg-green-100 text-green-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                2FA Enabled
            </span>
        @else
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-1 bg-indigo-100 text-indigo-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                Enable 2FA
            </a>
        @endif
    </div>

    {{-- Order Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="col-span-2 sm:col-span-1 lg:col-span-1 bg-white rounded-xl shadow p-5 text-center">
            <p class="text-3xl font-bold text-indigo-600">{{ $totalOrders }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Orders</p>
        </div>
        @php
            $statusColors = [
                'pending'    => 'text-yellow-600',
                'processing' => 'text-blue-600',
                'shipped'    => 'text-purple-600',
                'delivered'  => 'text-green-600',
                'cancelled'  => 'text-red-600',
            ];
        @endphp
        @foreach($orderStatuses as $status => $count)
            <div class="bg-white rounded-xl shadow p-5 text-center">
                <p class="text-3xl font-bold {{ $statusColors[$status] ?? 'text-gray-600' }}">{{ $count }}</p>
                <p class="text-sm text-gray-500 mt-1 capitalize">{{ $status }}</p>
            </div>
        @endforeach
    </div>

    {{-- Recent Orders + Recently Purchased Books --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Orders --}}
        <div class="bg-white rounded-xl shadow">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                <a href="{{ route('orders.index') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    View All
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentOrders as $order)
                    @php
                        $badgeColors = [
                            'pending'    => 'bg-yellow-100 text-yellow-800',
                            'processing' => 'bg-blue-100 text-blue-800',
                            'shipped'    => 'bg-purple-100 text-purple-800',
                            'delivered'  => 'bg-green-100 text-green-800',
                            'cancelled'  => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Order #{{ $order->id }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $order->orderItems->count() }} item(s) &middot; {{ $order->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-gray-900">
                                ${{ number_format($order->total_amount, 2) }}
                            </span>
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
                        You haven't placed any orders yet.
                        <a href="{{ route('books.index') }}" class="text-indigo-600 hover:underline ml-1">Browse books</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recently Purchased Books --}}
        <div class="bg-white rounded-xl shadow">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Recently Purchased</h2>
                <a href="{{ route('books.index') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    Browse More
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentBooks as $book)
                    <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}"
                                 alt="{{ $book->title }}"
                                 class="w-10 h-14 object-cover rounded shadow-sm shrink-0">
                        @else
                            <div class="w-10 h-14 bg-indigo-100 rounded flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $book->title }}</p>
                            <p class="text-xs text-gray-400">{{ $book->author ?? 'Unknown Author' }}</p>
                        </div>
                        <a href="{{ route('books.show', $book) }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 underline shrink-0">
                            View
                        </a>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">
                        No purchased books yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Review Activity --}}
    <div class="bg-white rounded-xl shadow">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">My Reviews</h2>
            <span class="text-sm text-gray-400">{{ $recentReviews->count() }} review(s)</span>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentReviews as $review)
                <div class="px-6 py-4 flex items-start justify-between hover:bg-gray-50 transition">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('books.show', $review->book) }}"
                           class="text-sm font-medium text-indigo-600 hover:underline">
                            {{ $review->book->title ?? 'Unknown Book' }}
                        </a>
                        <div class="flex text-yellow-400 text-xs mt-1">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <span>&#9733;</span>
                                @else
                                    <span class="text-gray-300">&#9733;</span>
                                @endif
                            @endfor
                            <span class="text-gray-400 ml-2">{{ $review->rating }}/5</span>
                        </div>
                        @if($review->comment)
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $review->comment }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 shrink-0 ml-4">
                        {{ $review->created_at->format('M d, Y') }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-400 text-sm">
                    You haven't submitted any reviews yet.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('books.index') }}"
               class="flex items-center gap-3 p-4 border-2 border-dashed border-gray-200 rounded-lg hover:border-indigo-400 hover:bg-indigo-50 transition group">
                <div class="bg-indigo-100 text-indigo-600 rounded-lg p-2 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Browse Books</p>
                    <p class="text-xs text-gray-400">Discover new titles</p>
                </div>
            </a>

            <a href="{{ route('orders.index') }}"
               class="flex items-center gap-3 p-4 border-2 border-dashed border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition group">
                <div class="bg-blue-100 text-blue-600 rounded-lg p-2 group-hover:bg-blue-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Order History</p>
                    <p class="text-xs text-gray-400">Track your orders</p>
                </div>
            </a>

            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 p-4 border-2 border-dashed border-gray-200 rounded-lg hover:border-green-400 hover:bg-green-50 transition group">
                <div class="bg-green-100 text-green-600 rounded-lg p-2 group-hover:bg-green-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Profile & Security</p>
                    <p class="text-xs text-gray-400">Manage your account</p>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection