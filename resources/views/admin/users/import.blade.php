@extends('layouts.app')

@section('title', 'Import Users - PageTurner')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Import Users</h1>
            <p class="text-sm text-gray-500 mt-1">Bulk create customer accounts from a CSV or Excel file.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.export') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                Switch to Export →
            </a>
            <a href="{{ route('admin.users.import.template') }}"
                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                ↓ Download Template
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Upload Form --}}
    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('admin.users.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                <input type="file" name="file" accept=".xlsx,.csv"
                    class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">Accepted formats: .xlsx, .csv — Max 10MB</p>
                @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Info box --}}
            <div class="bg-blue-50 rounded-lg p-4 text-sm text-blue-800">
                <p class="font-semibold mb-1">Required column headers:</p>
                <p class="font-mono text-xs">name, email, password, role, phone, address</p>
                <ul class="mt-2 text-xs text-blue-700 space-y-1 list-disc list-inside">
                    <li>Role must be <strong>customer</strong> — admin accounts cannot be bulk imported.</li>
                    <li>Duplicate emails will be skipped automatically.</li>
                    <li>All imported users will be marked as email verified.</li>
                    <li>Passwords must be at least 8 characters.</li>
                </ul>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">
                Upload & Import Users
            </button>
        </form>
    </div>

    {{-- Recent Import Logs --}}
    @if($logs->count() > 0)
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Recent Imports</h2>
            <div class="space-y-3">
                @foreach($logs as $log)
                    <div class="flex items-start justify-between border rounded-lg p-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $log->file_name }}</p>
                            <p class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y g:i A') }}</p>
                            @if($log->failed_rows > 0)
                                <p class="text-xs text-red-600 mt-1">{{ $log->failed_rows }} row(s) failed</p>
                                @if($log->failures)
                                    <details class="mt-1">
                                        <summary class="text-xs text-indigo-600 cursor-pointer">View errors</summary>
                                        <ul class="mt-1 space-y-1">
                                            @foreach($log->failures as $failure)
                                                <li class="text-xs text-gray-600">
                                                    Row {{ $failure['row'] }}: {{ implode(', ', $failure['errors']) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            @endif
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded
                            {{ $log->status === 'completed' ? 'bg-green-100 text-green-700' : ($log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($log->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection