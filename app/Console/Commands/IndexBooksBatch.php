<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

class IndexBooksBatch extends Command
{
    protected $signature   = 'scout:index-books-batch
                                {--chunk=500 : Number of records per batch}';
    protected $description = 'Index all active books in Scout with progress tracking';

    public function handle(): void
    {
        $this->info('🔍 Starting Scout batch indexing...');

        $chunkSize  = (int) $this->option('chunk');
        $total      = Book::where('is_active', true)->count();
        $startTime  = microtime(true);

        $this->info("Total active books to index: " . number_format($total));

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s%'
        );
        $bar->start();

        $indexed = 0;

        // Use lazy collection to avoid loading all records into memory
        Book::where('is_active', true)
            ->with('category:id,name')  // Eager load for toSearchableArray()
            ->orderBy('id')
            ->lazyById($chunkSize)
            ->each(function (Book $book) use ($bar, &$indexed) {
                $book->searchable();    // Add to Scout index
                $bar->advance();
                $indexed++;
            });

        $bar->finish();

        $elapsed = round(microtime(true) - $startTime, 2);

        $this->newLine(2);
        $this->info('✅ Indexing Complete!');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Indexed',       number_format($indexed)],
                ['Total Time',          $elapsed . ' seconds'],
                ['Avg Records/Second',  number_format($indexed / max($elapsed, 1))],
            ]
        );
    }
}