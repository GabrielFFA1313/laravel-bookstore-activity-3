@extends('layouts.app')

@section('title', 'Import Books - PageTurner')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Import Books</h1>
            <p class="text-sm text-gray-500 mt-1">Upload an Excel or CSV file to bulk-add books.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.books.export') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                Switch to Export →
            </a>
            <a href="{{ route('admin.books.import.template') }}"
                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                ↓ Download Template
            </a>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('admin.books.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- File Upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                <input type="file" name="file" accept=".xlsx,.csv"
                    class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">Accepted formats: .xlsx, .csv — Max 10MB</p>
                @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Duplicate Action --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">If a book with the same ISBN already exists:</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="duplicate_action" value="skip" checked
                            class="text-indigo-600 border-gray-300">
                        <span class="text-sm text-gray-700">Skip — keep the existing book unchanged</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="duplicate_action" value="update"
                            class="text-indigo-600 border-gray-300">
                        <span class="text-sm text-gray-700">Update — overwrite with imported data</span>
                    </label>
                </div>
                @error('duplicate_action') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Required Headers Info --}}
            <div class="bg-blue-50 rounded-lg p-4 text-sm text-blue-800">
                <p class="font-semibold mb-1">Required column headers:</p>
                <p class="font-mono text-xs">ISBN, Title, Author, Price, Stock, Category, Description</p>
                <p class="mt-2 text-xs text-blue-600">Category must match an existing category name. Description is optional.</p>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">
                Upload & Import
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