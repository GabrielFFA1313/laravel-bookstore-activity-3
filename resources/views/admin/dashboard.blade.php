@extends('layouts.app')

@section('title', 'Admin Dashboard - PageTurner')

@section('content')
<div class="space-y-10">

    {{-- Header --}}
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Admin Dashboard</h1>
            <p class="text-sm text-gray-400 mt-1 font-medium">
                Welcome back, {{ auth()->user()->name }}.
                <span class="text-indigo-500">Here's what's happening.</span>
            </p>
        </div>
        <span class="text-xs font-bold text-gray-400 bg-white border border-gray-100 px-4 py-2 rounded-full tracking-widest uppercase shadow-sm">
            {{ now()->format('l, F j Y') }}
        </span>
    </header>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Customers --}}
        <a href="{{ route('admin.users.index') }}"
           class="bg-white border border-gray-100 rounded-[2rem] p-5 flex items-center gap-4 shadow-sm border-b-4 border-b-indigo-100 hover:border-b-indigo-400 hover:shadow-indigo-100 hover:shadow-lg transition-all group">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em]">Customers</p>
                <h3 class="text-2xl font-black text-gray-900 tracking-tighter tabular-nums">{{ number_format($totalUsers) }}</h3>
            </div>
        </a>

        {{-- Books --}}
        <a href="{{ route('books.index') }}"
           class="bg-white border border-gray-100 rounded-[2rem] p-5 flex items-center gap-4 shadow-sm border-b-4 border-b-indigo-100 hover:border-b-indigo-400 hover:shadow-indigo-100 hover:shadow-lg transition-all group">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em]">Total Books</p>
                <h3 class="text-2xl font-black text-gray-900 tracking-tighter tabular-nums">{{ number_format($totalBooks) }}</h3>
            </div>
        </a>

        {{-- Orders --}}
        <a href="{{ route('orders.index') }}"
           class="bg-white border border-gray-100 rounded-[2rem] p-5 flex items-center gap-4 shadow-sm border-b-4 border-b-indigo-100 hover:border-b-indigo-400 hover:shadow-indigo-100 hover:shadow-lg transition-all group">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1em]">Total Orders</p>
                <h3 class="text-2xl font-black text-gray-900 tracking-tighter tabular-nums">{{ number_format($totalOrders) }}</h3>
            </div>
        </a>

        {{-- Revenue — solid indigo accent card --}}
        <div class="bg-indigo-600 border border-indigo-500 rounded-[2rem] p-5 flex items-center gap-4 shadow-lg shadow-indigo-200 border-b-4 border-b-indigo-800">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500 text-white flex items-center justify-center border border-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.1em]">Net Revenue</p>
                <h3 class="text-2xl font-black text-white tracking-tighter tabular-nums">${{ number_format($totalRevenue, 2) }}</h3>
            </div>
        </div>

    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Recent Orders --}}
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Recent Orders</h2>
                <a href="{{ route('orders.index') }}"
                   class="text-xs font-bold text-indigo-500 hover:text-indigo-700 tracking-widest uppercase transition-colors">
                    View All →
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentOrders as $order)
                    @php
                        $statusClass = match($order->status) {
                            'pending'    => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                            'processing' => 'bg-blue-50 text-blue-700 border border-blue-200',
                            'shipped'    => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                            'delivered'  => 'bg-green-50 text-green-700 border border-green-200',
                            'cancelled'  => 'bg-red-50 text-red-700 border border-red-200',
                            default      => 'bg-gray-50 text-gray-700 border border-gray-200',
                        };
                    @endphp
                    <div class="flex items-center justify-between p-4 bg-white border border-gray-50 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-200 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-indigo-50 rounded-full flex items-center justify-center font-black text-indigo-400 text-xs group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                {{ strtoupper(substr($order->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                    {{ $order->user->name ?? 'Unknown' }}
                                </h4>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    #{{ $order->id }} &bull; {{ $order->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-black text-gray-900 tabular-nums">
                                ${{ number_format($order->total_amount, 2) }}
                            </span>
                            <span class="text-[9px] font-black uppercase tracking-tighter px-2 py-1 rounded-full {{ $statusClass }}">
                                {{ $order->status }}
                            </span>
                            <a href="{{ route('orders.show', $order) }}"
                               class="text-gray-300 hover:text-indigo-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-gray-400 text-sm bg-white rounded-2xl border border-gray-50">
                        No orders yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">

            {{-- Operations Accordion --}}
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4 tracking-tight">Operations</h2>
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm divide-y divide-gray-50"
                     x-data="{ open: null }">

                    {{-- Books --}}
                    <div>
                        <button @click="open = open === 'books' ? null : 'books'"
                                class="w-full flex items-center justify-between px-5 py-4 hover:bg-indigo-50 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-gray-700 group-hover:text-indigo-700 uppercase tracking-wider transition-colors">Books</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition-all duration-200"
                                 :class="open === 'books' ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === 'books'" x-collapse class="bg-indigo-50/40 divide-y divide-indigo-100/60">
                            <a href="{{ route('admin.books.import') }}" class="flex items-center gap-3 px-6 py-3 text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Import Books
                            </a>
                            <a href="{{ route('admin.books.export') }}" class="flex items-center gap-3 px-6 py-3 text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Export Books
                            </a>
                        </div>
                    </div>

                    {{-- Orders --}}
                    <div>
                        <button @click="open = open === 'orders' ? null : 'orders'"
                                class="w-full flex items-center justify-between px-5 py-4 hover:bg-indigo-50 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-gray-700 group-hover:text-indigo-700 uppercase tracking-wider transition-colors">Orders</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition-all duration-200"
                                 :class="open === 'orders' ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === 'orders'" x-collapse class="bg-indigo-50/40 divide-y divide-indigo-100/60">
                            <a href="{{ route('admin.orders.export') }}" class="flex items-center gap-3 px-6 py-3 text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Export Orders
                            </a>
                            <a href="{{ route('admin.orders.revenue') }}" class="flex items-center gap-3 px-6 py-3 text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                Revenue Report
                            </a>
                        </div>
                    </div>

                    {{-- Users --}}
                    <div>
                        <button @click="open = open === 'users' ? null : 'users'"
                                class="w-full flex items-center justify-between px-5 py-4 hover:bg-indigo-50 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-gray-700 group-hover:text-indigo-700 uppercase tracking-wider transition-colors">Users</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition-all duration-200"
                                 :class="open === 'users' ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === 'users'" x-collapse class="bg-indigo-50/40 divide-y divide-indigo-100/60">
                            <a href="{{ route('admin.users.import') }}" class="flex items-center gap-3 px-6 py-3 text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Import Users
                            </a>
                            <a href="{{ route('admin.users.export') }}" class="flex items-center gap-3 px-6 py-3 text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Export Users
                            </a>
                        </div>
                    </div>

                    {{-- System --}}
                    <div>
                        <button @click="open = open === 'system' ? null : 'system'"
                                class="w-full flex items-center justify-between px-5 py-4 hover:bg-indigo-50 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-gray-700 group-hover:text-indigo-700 uppercase tracking-wider transition-colors">System</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition-all duration-200"
                                 :class="open === 'system' ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === 'system'" x-collapse class="bg-indigo-50/40 divide-y divide-indigo-100/60">
                            <a href="{{ route('admin.backup.index') }}" class="flex items-center gap-3 px-6 py-3 text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                                Backup & Maintenance
                            </a>
                            <a href="{{ route('admin.audit.index') }}" class="flex items-center gap-3 px-6 py-3 text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Audit Log
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Latest Review --}}
            @if($recentReviews->isNotEmpty())
            <div class="p-6 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-amber-300 fill-amber-300" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="text-sm font-bold text-white">Latest Review</span>
                </div>
                <p class="text-xs text-indigo-200 italic leading-relaxed mb-4 line-clamp-3">
                    "{{ $recentReviews->first()->comment }}"
                </p>
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-white">
                            {{ $recentReviews->first()->user->name ?? 'Unknown' }}
                        </span>
                        <span class="text-[9px] font-medium text-indigo-300 uppercase">
                            {{ $recentReviews->first()->created_at->diffForHumans() }}
                        </span>
                    </div>
                    @if($recentReviews->first() && $recentReviews->first()->book)
                     <a href="{{ route('books.show', $recentReviews->first()->book) }}"
                       class="text-[10px] font-bold text-indigo-200 hover:text-white transition-colors underline underline-offset-2">
                        View Book
                    </a>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection