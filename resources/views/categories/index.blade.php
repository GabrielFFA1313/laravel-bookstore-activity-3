@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Categories</h1>
        @auth
            @if(auth()->user()->isAdmin())
                <div class="flex gap-2">
                    <a href="{{ route('books.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Add Book
                    </a>
                    <a href="{{ route('categories.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Add Category
                    </a>
                </div>
            @endif
        @endauth
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($categories as $category)
            <div class="bg-white rounded-lg shadow p-6 flex flex-col">  
                    <h3 class="text-xl font-semibold mb-2">{{ $category->name }}</h3>
                    
                    @if($category->description)
                        <p class="text-gray-600 mb-4">{{ $category->description }}</p>
                    @endif
                    
                    <p class="text-sm text-gray-500 mb-4">{{ $category->books_count }} books</p>
                    
                    <div class="flex flex-wrap gap-2 mt-auto"> 
                        
                    <a href="{{ route('categories.show', $category) }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        View Books
                    </a>
                    
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('categories.edit', $category) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                                Edit
                            </a>
                            
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $categories->links() }}
    </div>
@endsection