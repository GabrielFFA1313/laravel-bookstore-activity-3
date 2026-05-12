@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🤖 AI Review Analysis</h1>
        <button onclick="bulkAnalyze()"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            ⚡ Bulk Analyze All Books
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 shadow text-center">
            <p class="text-3xl font-bold text-gray-800">{{ $stats['total_analyzed'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Analyzed</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4 shadow text-center">
            <p class="text-3xl font-bold text-green-600">{{ $stats['positive'] }}</p>
            <p class="text-sm text-gray-500 mt-1">😊 Positive</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-4 shadow text-center">
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['neutral'] }}</p>
            <p class="text-sm text-gray-500 mt-1">😐 Neutral</p>
        </div>
        <div class="bg-red-50 rounded-xl p-4 shadow text-center">
            <p class="text-3xl font-bold text-red-600">{{ $stats['negative'] }}</p>
            <p class="text-sm text-gray-500 mt-1">😞 Negative</p>
        </div>
    </div>

    {{-- Summaries Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Book</th>
                    <th class="px-4 py-3 text-left">Sentiment</th>
                    <th class="px-4 py-3 text-left">Summary</th>
                    <th class="px-4 py-3 text-left">Reviews</th>
                    <th class="px-4 py-3 text-left">Provider</th>
                    <th class="px-4 py-3 text-left">Last Analyzed</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($summaries as $summary)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ Str::limit($summary->book->title ?? 'N/A', 30) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $summary->sentiment === 'positive' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $summary->sentiment === 'neutral'  ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $summary->sentiment === 'negative' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $summary->sentiment_emoji }} {{ ucfirst($summary->sentiment) }}
                            ({{ number_format($summary->sentiment_score * 100) }}%)
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ Str::limit($summary->summary, 80) }}
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $summary->reviews_analyzed }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-xs">
                            {{ ucfirst($summary->ai_provider) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">
                        {{ $summary->last_analyzed_at?->diffForHumans() ?? 'Never' }}
                    </td>
                    <td class="px-4 py-3">
                        <button onclick="analyzeBook({{ $summary->book_id }})"
                            class="text-blue-600 hover:underline text-xs">
                            Re-analyze
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        No books analyzed yet. Click "Bulk Analyze" to get started!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 border-t">
            {{ $summaries->links() }}
        </div>
    </div>
</div>

{{-- Toast notification --}}
<div id="toast" class="hidden fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg text-sm"></div>

<script>
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 3000);
}

function analyzeBook(bookId) {
    fetch(`/admin/books/${bookId}/analyze`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(d => showToast(d.message));
}

function bulkAnalyze() {
    if (!confirm('Queue all books for analysis? This may take a while.')) return;
    fetch('/admin/books/bulk-analyze', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(d => showToast(d.message));
}
</script>
@endsection