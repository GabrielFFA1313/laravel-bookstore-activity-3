@extends('layouts.app')

@section('title', 'Audit Log Detail - PageTurner')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.audit.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
            ← Back to Audit Logs
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-6 space-y-6">

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Audit Log Detail</h1>
                <p class="text-xs text-gray-400 mt-1 font-mono">{{ $audit->id }}</p>
            </div>
            {{-- Integrity badge --}}
            @if($integrityOk)
                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                    ✓ Checksum Valid
                </span>
            @else
                <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                    ⚠ Checksum Mismatch — Possible Tampering
                </span>
            @endif
        </div>

        {{-- Event Info --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Event</p>
                <span class="px-2 py-0.5 rounded text-sm font-semibold bg-indigo-100 text-indigo-700">
                    {{ ucfirst(str_replace('_', ' ', $audit->event)) }}
                </span>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Model</p>
                <p class="text-sm text-gray-900">
                    {{ $audit->auditable_type }}
                    @if($audit->auditable_id) #{{ $audit->auditable_id }} @endif
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">User</p>
                @if($audit->user_name)
                    <p class="text-sm font-medium text-gray-900">{{ $audit->user_name }}</p>
                    <p class="text-xs text-gray-400">{{ $audit->user_email }}</p>
                @else
                    <p class="text-sm text-gray-400">System / Unauthenticated</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date</p>
                <p class="text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($audit->created_at)->format('F d, Y g:i:s A') }}
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">IP Address</p>
                <p class="text-sm text-gray-900 font-mono">{{ $audit->ip_address ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">User Agent</p>
                <p class="text-xs text-gray-600 break-all">{{ $audit->user_agent ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">URL</p>
                <p class="text-sm text-gray-600 break-all font-mono">{{ $audit->url ?? '—' }}</p>
            </div>
        </div>

        {{-- Old vs New Values --}}
        @php
            $oldValues = json_decode($audit->old_values, true) ?? [];
            $newValues = json_decode($audit->new_values, true) ?? [];
        @endphp

        @if(!empty($oldValues) || !empty($newValues))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if(!empty($oldValues))
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Old Values</p>
                        <div class="bg-red-50 rounded-lg p-4 text-sm font-mono space-y-1">
                            @foreach($oldValues as $key => $value)
                                <div class="flex gap-2">
                                    <span class="text-red-400 font-semibold min-w-24">{{ $key }}:</span>
                                    <span class="text-red-700">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($newValues))
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">New Values</p>
                        <div class="bg-green-50 rounded-lg p-4 text-sm font-mono space-y-1">
                            @foreach($newValues as $key => $value)
                                <div class="flex gap-2">
                                    <span class="text-green-600 font-semibold min-w-24">{{ $key }}:</span>
                                    <span class="text-green-800">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Checksum --}}
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Checksum (SHA-256)</p>
            <p class="text-xs text-gray-400 font-mono break-all">{{ $audit->checksum ?? 'N/A' }}</p>
        </div>

    </div>
</div>
@endsection