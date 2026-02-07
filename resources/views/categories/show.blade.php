@extends('layouts.app')

@section('title', $category->name . ' - PageTurner')

@section('content')
    <div class="mb-6">
        <a href="{{ route('categories.index') }}" class="text-indigo-600 hover:text-indigo-800">
            ← Back to Categories
        </a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $category->name }}</h1>
        
        @if($category->description)
            <p class="text-gray-600">{{ $category->description }}</p>
        @endif
        
        <p class="text-sm text-gray-500 mt-2">{{ $books->total() }} books in this category</p>
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
            No books found in this category yet.
        </x-alert>
    @endif
@endsection