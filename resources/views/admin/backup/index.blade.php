@extends('layouts.app')

@section('title', 'Backup Management - PageTurner')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Backup Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage database and file backups.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Manual Backup Triggers --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Run Manual Backup</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- DB Only Backup --}}
            <form action="{{ route('admin.backup.run') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="db">
                <button type="submit"
                    onclick="return confirm('Run a database-only backup now?')"
                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 transition font-medium text-sm">
                    🗄 Database Backup
                    <p class="text-xs font-normal opacity-80 mt-0.5">Database dump only</p>
                </button>
            </form>

            {{-- Full Backup --}}
            <form action="{{ route('admin.backup.run') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="full">
                <button type="submit"
                    onclick="return confirm('Run a full backup now? This may take a while.')"
                    class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition font-medium text-sm">
                    📦 Full Backup
                    <p class="text-xs font-normal opacity-80 mt-0.5">Database + uploaded files</p>
                </button>
            </form>

            {{-- Cleanup --}}
            <form action="{{ route('admin.backup.clean') }}" method="POST">
                @csrf
                <button type="submit"
                    onclick="return confirm('Clean up old backups based on retention policy?')"
                    class="w-full bg-red-50 text-red-700 border border-red-200 py-3 px-4 rounded-lg hover:bg-red-100 transition font-medium text-sm">
                    🧹 Clean Old Backups
                    <p class="text-xs font-normal opacity-60 mt-0.5">Enforce retention policy</p>
                </button>
            </form>

        </div>
    </div>

    {{-- Schedule Info --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Backup Schedule</h2>
        <div class="space-y-3">
            <div class="flex items-center justify-between py-2 border-b">
                <div>
                    <p class="text-sm font-medium text-gray-900">Daily Database Backup</p>
                    <p class="text-xs text-gray-500">Runs every day at 2:00 AM</p>
                </div>
                <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-2 py-0.5 rounded">Daily</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b">
                <div>
                    <p class="text-sm font-medium text-gray-900">Weekly Full Backup</p>
                    <p class="text-xs text-gray-500">Runs every Sunday at 2:30 AM</p>
                </div>
                <span class="text-xs bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded">Weekly</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b">
                <div>
                    <p class="text-sm font-medium text-gray-900">Backup Cleanup</p>
                    <p class="text-xs text-gray-500">Runs every day at 3:00 AM — keeps 7 daily, 4 weekly, 12 monthly</p>
                </div>
                <span class="text-xs bg-yellow-100 text-yellow-700 font-semibold px-2 py-0.5 rounded">Daily</span>
            </div>
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="text-sm font-medium text-gray-900">Health Monitor</p>
                    <p class="text-xs text-gray-500">Runs every day at 3:30 AM — alerts if backup is missing or too large</p>
                </div>
                <span class="text-xs bg-gray-100 text-gray-700 font-semibold px-2 py-0.5 rounded">Daily</span>
            </div>
        </div>
    </div>

    {{-- Existing Backups --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Stored Backups</h2>

        @if(count($backups) > 0)
            <div class="space-y-2">
                @foreach($backups as $backup)
                    <div class="flex items-center justify-between border rounded-lg px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $backup['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $backup['modified'] }} · {{ $backup['size'] }}</p>
                        </div>
                        <a href="{{ route('admin.backup.download', ['filename' => $backup['name']]) }}"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                            ↓ Download
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-6">No backups found yet. Run a backup to get started.</p>
        @endif
    </div>

</div>
@endsection