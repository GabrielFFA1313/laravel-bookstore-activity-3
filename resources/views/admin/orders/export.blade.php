@extends('layouts.app')

@section('title', 'Export Orders - PageTurner')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Export Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Download filtered order data as CSV.</p>
        </div>
        <a href="{{ route('admin.orders.revenue') }}"
            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            Revenue Reports →
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('admin.orders.export.download') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg text-sm">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                {{-- Customer search --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name / Email</label>
                    <input type="text" name="customer" placeholder="Search customer..."
                        class="w-full border-gray-300 rounded-lg text-sm">
                </div>

                {{-- Date range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" class="w-full border-gray-300 rounded-lg text-sm">
                </div>

            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">
                ↓ Download CSV
            </button>
        </form>
    </div>

</div>
@endsection