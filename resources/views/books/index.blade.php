@extends('layouts.app')

@section('title', 'All Books - PageTurner')

@section('content')
    <h1 class="text-3xl font-bold mb-6">All Books</h1>

    
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.books.import') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                    Import Books
                </a>
                <a href="{{ route('admin.books.export') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                    Export CSV
                </a>
            @endif

    {{-- Advanced Search and Filters --}}
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form action="{{ route('books.index') }}" method="GET">
            {{-- Search Bar --}}
            <div class="mb-4">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search"
                    value="{{ request('search') }}" 
                    placeholder="Search by title, author, or description..." 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                {{-- Category Filter --}}
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select 
                        name="category" 
                        id="category"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                

                {{-- Price Range --}}
                <div>
                    <label for="min_price" class="block text-sm font-medium text-gray-700 mb-2">Min Price</label>
                    <input 
                        type="number" 
                        name="min_price" 
                        id="min_price"
                        value="{{ request('min_price') }}" 
                        step="0.01" 
                        min="0"
                        placeholder="$0.00"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <div>
                    <label for="max_price" class="block text-sm font-medium text-gray-700 mb-2">Max Price</label>
                    <input 
                        type="number" 
                        name="max_price" 
                        id="max_price"
                        value="{{ request('max_price') }}" 
                        step="0.01" 
                        min="0"
                        placeholder="$999.99"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                {{-- Rating Filter --}}
                <div>
                    <label for="min_rating" class="block text-sm font-medium text-gray-700 mb-2">Min Rating</label>
                    <select 
                        name="min_rating" 
                        id="min_rating"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Any Rating</option>
                        <option value="4" {{ request('min_rating') == 4 ? 'selected' : '' }}>4+ Stars</option>
                        <option value="3" {{ request('min_rating') == 3 ? 'selected' : '' }}>3+ Stars</option>
                        <option value="2" {{ request('min_rating') == 2 ? 'selected' : '' }}>2+ Stars</option>
                        <option value="1" {{ request('min_rating') == 1 ? 'selected' : '' }}>1+ Stars</option>
                    </select>
                </div>
            </div>

            {{-- Sort Options --}}
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select 
                        name="sort" 
                        id="sort"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">
                        Apply Filters
                    </button>
                    <a href="{{ route('books.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition">
                        Clear All
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Results Count --}}
    <div class="mb-4">
        <p class="text-gray-600">
            Showing {{ $books->count() }} of {{ $books->total() }} books
            @if(request('search'))
                for "<strong>{{ request('search') }}</strong>"
            @endif
        </p>
    </div>

    {{-- Books Grid --}}
    @if($books->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($books as $book)
                <x-book-card :book="$book" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $books->links() }}
        </div>
    @else
        <x-alert type="info">
            No books found matching your criteria. Try adjusting your filters.
        </x-alert>
    @endif
@endsection