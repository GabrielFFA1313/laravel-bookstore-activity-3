@extends('layouts.app')

@section('title', 'Export Users - PageTurner')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Export Users</h1>
            <p class="text-sm text-gray-500 mt-1">Download customer account data as CSV.</p>
        </div>
        <a href="{{ route('admin.users.import') }}"
            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            ← Switch to Import
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('admin.users.export.download') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Verification status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Verification</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg text-sm">
                        <option value="">All Users</option>
                        <option value="verified">Verified Only</option>
                        <option value="unverified">Unverified Only</option>
                    </select>
                </div>

                {{-- Search --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Name / Email</label>
                    <input type="text" name="search" placeholder="Filter by name or email..."
                        class="w-full border-gray-300 rounded-lg text-sm">
                </div>

                {{-- Date range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registered From</label>
                    <input type="date" name="date_from" class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registered To</label>
                    <input type="date" name="date_to" class="w-full border-gray-300 rounded-lg text-sm">
                </div>

            </div>

            <div class="bg-yellow-50 rounded-lg p-3 text-xs text-yellow-800">
                Exported columns: ID, Name, Email, Phone, Email Verified, Registered At, Total Orders
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">
                ↓ Download CSV
            </button>
        </form>
    </div>

    {{-- Recent Export Logs --}}
    @if($logs->count() > 0)
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Recent Exports</h2>
            <div class="space-y-3">
                @foreach($logs as $log)
                    <div class="flex items-start justify-between border rounded-lg p-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $log->file_name }}</p>
                            <p class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-green-100 text-green-700">
                            Completed
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection