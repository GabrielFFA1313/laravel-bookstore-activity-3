@extends('layouts.app')

@section('title', 'Revenue Reports - PageTurner')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Revenue Reports</h1>
            <p class="text-sm text-gray-500 mt-1">Export financial summaries as CSV.</p>
        </div>
        <a href="{{ route('admin.orders.export') }}"
            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            ← Order Exports
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">All-Time Revenue</p>
            <p class="text-xl font-bold text-indigo-600">${{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Orders</p>
            <p class="text-xl font-bold text-gray-900">{{ $totalOrders }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">This Month</p>
            <p class="text-xl font-bold text-indigo-600">${{ number_format($monthRevenue, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Orders This Month</p>
            <p class="text-xl font-bold text-gray-900">{{ $monthOrders }}</p>
        </div>
    </div>

    {{-- Export Form --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Export Revenue Report</h2>
        <form action="{{ route('admin.orders.revenue.download') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From <span class="text-red-500">*</span></label>
                    <input type="date" name="date_from" required
                        class="w-full border-gray-300 rounded-lg text-sm"
                        value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To <span class="text-red-500">*</span></label>
                    <input type="date" name="date_to" required
                        class="w-full border-gray-300 rounded-lg text-sm"
                        value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group Results By</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="group_by" value="daily" checked
                            class="text-indigo-600 border-gray-300">
                        <span class="text-sm text-gray-700">Daily</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="group_by" value="monthly"
                            class="text-indigo-600 border-gray-300">
                        <span class="text-sm text-gray-700">Monthly</span>
                    </label>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">
                ↓ Download Revenue CSV
            </button>
        </form>
    </div>

</div>
@endsection