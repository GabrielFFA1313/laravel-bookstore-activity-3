@extends('layouts.app')

@section('title', 'My Data - PageTurner')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Data & Exports</h1>
        <p class="text-sm text-gray-500 mt-1">Download your personal data and order history.</p>
    </div>

    {{-- GDPR Notice --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
        <p class="font-semibold mb-1">Your Data Rights</p>
        <p>Under GDPR and data protection laws, you have the right to access and download all personal data we hold about you. Use the options below to export your data at any time.</p>
    </div>

    {{-- Export Options --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Full Data Export --}}
        <div class="bg-white rounded-xl shadow p-5">
            <div class="mb-3">
                <h2 class="text-base font-semibold text-gray-900">Full Data Export</h2>
                <p class="text-xs text-gray-500 mt-1">Download all your personal data including account info, addresses, orders, and reviews in JSON format.</p>
            </div>
            <a href="{{ route('customer.data.export') }}"
                class="block w-full text-center bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                ↓ Download My Data (JSON)
            </a>
        </div>

        {{-- Reading History --}}
        <div class="bg-white rounded-xl shadow p-5">
            <div class="mb-3">
                <h2 class="text-base font-semibold text-gray-900">Reading History</h2>
                <p class="text-xs text-gray-500 mt-1">Export a list of all books you have purchased, including titles, authors, and categories.</p>
            </div>
            <a href="{{ route('customer.data.reading-history') }}"
                class="block w-full text-center bg-green-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                ↓ Download Reading History (JSON)
            </a>
        </div>

        {{-- Orders PDF --}}
        <div class="bg-white rounded-xl shadow p-5">
            <div class="mb-3">
                <h2 class="text-base font-semibold text-gray-900">Order History (PDF)</h2>
                <p class="text-xs text-gray-500 mt-1">Download a formatted PDF of all your orders, suitable for records or reimbursement claims.</p>
            </div>
            <a href="{{ route('customer.data.orders.pdf') }}"
                class="block w-full text-center bg-red-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                ↓ Download Orders PDF
            </a>
        </div>

        {{-- Orders CSV --}}
        <div class="bg-white rounded-xl shadow p-5">
            <div class="mb-3">
                <h2 class="text-base font-semibold text-gray-900">Order History (CSV)</h2>
                <p class="text-xs text-gray-500 mt-1">Download your order history as a spreadsheet, compatible with Excel and Google Sheets.</p>
            </div>
            <a href="{{ route('customer.data.orders.csv') }}"
                class="block w-full text-center bg-gray-700 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                ↓ Download Orders CSV
            </a>
        </div>

    </div>

    {{-- What's included --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="text-base font-semibold text-gray-900 mb-3">What's included in your data export?</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <span class="text-green-500">✓</span> Account information
            </div>
            <div class="flex items-center gap-2">
                <span class="text-green-500">✓</span> Saved addresses
            </div>
            <div class="flex items-center gap-2">
                <span class="text-green-500">✓</span> Complete order history
            </div>
            <div class="flex items-center gap-2">
                <span class="text-green-500">✓</span> Book reviews
            </div>
            <div class="flex items-center gap-2">
                <span class="text-green-500">✓</span> Purchase history
            </div>
            <div class="flex items-center gap-2">
                <span class="text-red-400">✗</span> Passwords (never exported)
            </div>
        </div>
    </div>

</div>
@endsection