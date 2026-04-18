@extends('layouts.app')

@section('title', 'Audit Logs - PageTurner')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Track all system and user activity.</p>
        </div>
        {{-- Export Buttons --}}
        <div class="flex gap-2">
            <a href="{{ route('admin.audit.export.csv', request()->query()) }}"
                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                ↓ Export CSV
            </a>
            <a href="{{ route('admin.audit.export.pdf', request()->query()) }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                ↓ Export PDF
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow p-5">
        <form action="{{ route('admin.audit.index') }}" method="GET"
            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">

            <input type="text" name="search" placeholder="Search..."
                value="{{ request('search') }}"
                class="border-gray-300 rounded-lg text-sm col-span-2">

            <select name="event" class="border-gray-300 rounded-lg text-sm">
                <option value="">All Events</option>
                @foreach($events as $event)
                    <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $event)) }}
                    </option>
                @endforeach
            </select>

            <input type="text" name="auditable_type" placeholder="Model (e.g. Book)"
                value="{{ request('auditable_type') }}"
                class="border-gray-300 rounded-lg text-sm">

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="border-gray-300 rounded-lg text-sm">

            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="border-gray-300 rounded-lg text-sm">

            <div class="col-span-2 sm:col-span-3 lg:col-span-6 flex gap-2">
                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Filter
                </button>
                <a href="{{ route('admin.audit.index') }}"
                    class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Audit Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Event</th>
                    <th class="px-4 py-3 text-left">Model</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">IP Address</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($audits as $audit)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                @if(in_array($audit->event, ['deleted', 'login_failed', 'backup_failed']))
                                    bg-red-100 text-red-700
                                @elseif(in_array($audit->event, ['created', 'login', 'backup_success']))
                                    bg-green-100 text-green-700
                                @elseif(in_array($audit->event, ['updated', 'status_changed']))
                                    bg-blue-100 text-blue-700
                                @else
                                    bg-gray-100 text-gray-700
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $audit->event)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ class_basename($audit->auditable_type) }}
                            @if($audit->auditable_id)
                                <span class="text-gray-400">#{{ $audit->auditable_id }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($audit->user_name)
                                <p class="font-medium text-gray-900">{{ $audit->user_name }}</p>
                                <p class="text-xs text-gray-400">{{ $audit->user_email }}</p>
                            @else
                                <span class="text-gray-400 text-xs">System</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $audit->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ \Carbon\Carbon::parse($audit->created_at)->format('M d, Y g:i A') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.audit.show', $audit->id) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                View →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            No audit logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($audits->hasPages())
        <div>{{ $audits->appends(request()->query())->links() }}</div>
    @endif

</div>
@endsection