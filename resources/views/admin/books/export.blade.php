@extends('layouts.app')

@section('title', 'Export Books - PageTurner')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Export Books</h1>
            <p class="text-sm text-gray-500 mt-1">Download your book catalog as a CSV file.</p>
        </div>
        <a href="{{ route('admin.books.import') }}"
            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            Switch to Import →
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Export Form --}}
    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('admin.books.export.download') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Column Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Columns to Export <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([
                        'isbn'        => 'ISBN',
                        'title'       => 'Title',
                        'author'      => 'Author',
                        'price'       => 'Price',
                        'stock'       => 'Stock',
                        'category'    => 'Category',
                        'description' => 'Description',
                        'created_at'  => 'Created At',
                    ] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="columns[]" value="{{ $value }}"
                                checked class="rounded border-gray-300 text-indigo-600">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('columns') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <hr>

            {{-- Filters --}}
            <div>
                <p class="text-sm font-medium text-gray-700 mb-3">Filters <span class="text-gray-400 font-normal">(optional)</span></p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Category --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <select name="category_id" class="w-full border-gray-300 rounded-lg text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Stock Status --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stock Status</label>
                        <select name="stock_status" class="w-full border-gray-300 rounded-lg text-sm">
                            <option value="all">All</option>
                            <option value="in_stock">In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>

                    {{-- Price Range --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Min Price</label>
                        <input type="number" name="price_min" step="0.01" min="0" placeholder="0.00"
                            class="w-full border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Max Price</label>
                        <input type="number" name="price_max" step="0.01" min="0" placeholder="9999.99"
                            class="w-full border-gray-300 rounded-lg text-sm">
                    </div>

                    {{-- Date Range --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Added From</label>
                        <input type="date" name="date_from"
                            class="w-full border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Added To</label>
                        <input type="date" name="date_to"
                            class="w-full border-gray-300 rounded-lg text-sm">
                    </div>

                </div>
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
                            @if($log->filters)
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Filters: {{ collect($log->filters)->filter()->keys()->implode(', ') ?: 'none' }}
                                </p>
                            @endif
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