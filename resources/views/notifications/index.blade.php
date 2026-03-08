@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Notifications</h1>
            <div class="flex gap-3">
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                            Mark All as Read
                        </button>
                    </form>
                @endif

                @if($notifications->total() > 0)
                    <form method="POST" action="{{ route('notifications.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm"
                            onclick="return confirm('Clear all notifications?')">
                            Clear All
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Status message --}}
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-md">
                {{ session('status') }}
            </div>
        @endif

        {{-- Notifications List --}}
        @if($notifications->count() > 0)
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    <div class="bg-white rounded-lg shadow p-4 flex items-start justify-between
                        {{ is_null($notification->read_at) ? 'border-l-4 border-indigo-500' : '' }}">

                        {{-- Icon + Content --}}
                        <div class="flex items-start gap-4">
                            <span class="text-2xl">
                                {{ $notification->data['icon'] ?? '🔔' }}
                            </span>
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                    @if(is_null($notification->read_at))
                                        <span class="ml-2 px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700 rounded-full">
                                            New
                                        </span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 ml-4 shrink-0">
                            @if(is_null($notification->read_at))
                                <form method="POST"
                                    action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="text-xs text-indigo-600 hover:text-indigo-900 underline">
                                        Mark Read
                                    </button>
                                </form>
                            @endif

                            <form method="POST"
                                action="{{ route('notifications.destroy', $notification->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-xs text-red-600 hover:text-red-900 underline">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>

        @else
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md text-center">
                You have no notifications.
            </div>
        @endif
    </div>
@endsection