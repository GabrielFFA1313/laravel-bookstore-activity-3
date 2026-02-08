@extends('layouts.app')

@section('title', 'Add New Category - PageTurner')

@section('content')
    <div class="mb-6">
        <a href="{{ route('categories.index') }}" class="text-indigo-600 hover:text-indigo-800">
            ← Back to Categories
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Add New Category</h1>

            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium mb-2">Category Name *</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        value="{{ old('name') }}" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror" 
                        placeholder="e.g., Science Fiction"
                        required
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="4" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                        placeholder="Optional description for this category"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('categories.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection