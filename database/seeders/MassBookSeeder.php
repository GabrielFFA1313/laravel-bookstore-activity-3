<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MassBookSeeder extends Seeder
{
    // ─────────────────────────────────────────────
    // CONSTANTS
    // ─────────────────────────────────────────────

    /**
     * Optimal chunk size for PostgreSQL batch inserts.
     * Too large = memory spike. Too small = too many round trips.
     * 5000 is the sweet spot for both MySQL and PostgreSQL.
     */
    private const CHUNK_SIZE = 5000;

    /**
     * Target: 1,000,000 book records.
     */
    private const TOTAL_RECORDS = 1000000;

    // ─────────────────────────────────────────────
    // MAIN RUN METHOD
    // ─────────────────────────────────────────────

    public function run(): void
    {
        $this->command->info('📚 Starting 1 Million Book Seeder...');
        $this->command->info('⚠️  Do NOT interrupt — this may take several minutes.');

        // Disable query logging to save memory during bulk insert
        DB::disableQueryLog();

        $inserted  = 0;
        $startTime = microtime(true);
        $totalChunks = ceil(self::TOTAL_RECORDS / self::CHUNK_SIZE);
        $currentChunk = 0;

        // Progress bar for visual feedback in terminal
        $bar = $this->command->getOutput()->createProgressBar(self::TOTAL_RECORDS);
        $bar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s% | ETA: %estimated:-6s%'
        );
        $bar->start();

        // ─────────────────────────────────────────
        // CORE SEEDING LOOP
        // ─────────────────────────────────────────
        while ($inserted < self::TOTAL_RECORDS) {

            // Handle last chunk — may be smaller than CHUNK_SIZE
            $batchSize = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - $inserted);

            // make() generates models WITHOUT persisting to DB
            // toArray() converts to plain arrays for raw insert
            $books = Book::factory()->count($batchSize)->make()->toArray();

            // Raw DB insert — bypasses Eloquent overhead completely
            // This is ~10x faster than ::create() per record
            DB::table('books')->insert($books);

            $inserted += $batchSize;
            $currentChunk++;

            // Advance progress bar
            $bar->advance($batchSize);

            // ─────────────────────────────────────
            // GARBAGE COLLECTION every 10 chunks
            // Prevents memory from accumulating across iterations
            // ─────────────────────────────────────
            if ($inserted % (self::CHUNK_SIZE * 10) === 0) {
                unset($books);
                gc_collect_cycles();

                // Optional: log memory usage every 10 chunks
                $memoryMB = round(memory_get_usage(true) / 1024 / 1024, 2);
                $this->command->getOutput()->writeln(
                    "\n  💾 Memory: {$memoryMB} MB | Inserted: " .
                    number_format($inserted) . " / " .
                    number_format(self::TOTAL_RECORDS)
                );
            }
        }

        // ─────────────────────────────────────────
        // COMPLETION SUMMARY
        // ─────────────────────────────────────────
        $bar->finish();

        $elapsed     = round(microtime(true) - $startTime, 2);
        $finalMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $this->command->newLine(2);
        $this->command->info('✅ Seeding Complete!');
        $this->command->table(
            ['Metric', 'Value'],
            [
                ['Total Records Inserted', number_format(self::TOTAL_RECORDS)],
                ['Total Time',             $elapsed . ' seconds'],
                ['Peak Memory Usage',      $finalMemory . ' MB'],
                ['Avg Records/Second',     number_format(self::TOTAL_RECORDS / $elapsed)],
            ]
        );

        // Warn if memory limit was exceeded
        if ($finalMemory > 512) {
            $this->command->warn('⚠️  Peak memory exceeded 512 MB! Consider reducing CHUNK_SIZE.');
        } else {
            $this->command->info('✅ Memory usage within 512 MB limit.');
        }
    }
}