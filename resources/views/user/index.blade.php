@extends('layouts.app')

@section('title', 'User Management - PageTurner')

@section('content')
    <h1 class="text-3xl font-bold mb-6">User Management</h1>

    {{-- Success / Error Messages --}}
    @if (session('status'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-md">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-md">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Search and Filter --}}
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form action="{{ route('admin.users.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Search --}}
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name or email..."
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                {{-- Filter --}}
                <div>
                    <label for="filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select
                        name="filter"
                        id="filter"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All Active Users</option>
                        <option value="verified"     {{ request('filter') === 'verified'     ? 'selected' : '' }}>Verified</option>
                        <option value="unverified"   {{ request('filter') === 'unverified'   ? 'selected' : '' }}>Unverified</option>
                        <option value="deactivated"  {{ request('filter') === 'deactivated'  ? 'selected' : '' }}>Deactivated</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">
                    Apply Filters
                </button>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition">
                    Clear All
                </a>
            </div>
        </form>
    </div>

    {{-- Results Count --}}
    <div class="mb-4">
        <p class="text-gray-600">
            Showing {{ $users->count() }} of {{ $users->total() }} users
            @if(request('search'))
                for "<strong>{{ request('search') }}</strong>"
            @endif
        </p>
    </div>

    {{-- Users Table --}}
    @if($users->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr class="{{ $user->trashed() ? 'bg-red-50' : '' }}">

                            {{-- ID --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                #{{ $user->id }}
                            </td>

                            {{-- Name --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm mr-3">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $user->email }}
                            </td>

                            {{-- Joined --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>

                            {{-- Status Badges --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->trashed())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Deactivated
                                    </span>
                                @elseif($user->hasVerifiedEmail())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Verified
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Unverified
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">

                                    {{-- Verify button (only for active unverified users) --}}
                                    @if(! $user->trashed() && ! $user->hasVerifiedEmail())
                                        <form method="POST" action="{{ route('admin.users.verify', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="text-indigo-600 hover:text-indigo-900 transition"
                                                onclick="return confirm('Manually verify {{ $user->name }}?')"
                                            >
                                                Verify
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Deactivate button (only for active users) --}}
                                    @if(! $user->trashed())
                                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 transition"
                                                onclick="return confirm('Deactivate {{ $user->name }}? They can be restored later.')"
                                            >
                                                Deactivate
                                            </button>
                                        </form>

                                    {{-- Restore button (only for deactivated users) --}}
                                    @else
                                        <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="text-green-600 hover:text-green-900 transition"
                                                onclick="return confirm('Restore {{ $user->name }}?')"
                                            >
                                                Restore
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $users->links() }}
        </div>

    @else
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md">
            No users found matching your criteria.
        </div>
    @endif

@endsection