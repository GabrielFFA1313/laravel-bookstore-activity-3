<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Repositories\BookRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BenchmarkBookQueries extends Command
{
    protected $signature = 'benchmark:books
                            {--iterations=100 : Number of times to run each query}
                            {--warmup=5       : Warmup passes before measuring}';

    protected $description = 'Benchmark critical book queries against performance targets';

    // ── Performance targets from Section 3.2.2 ───────────────────────────
    private const TARGETS = [
        'catalog_listing'  => 100,   // < 100ms
        'isbn_lookup'      => 50,    // < 50ms
        'category_filter'  => 150,   // < 150ms
        'fulltext_search'  => 300,   // < 300ms
        'price_range'      => 100,   // < 100ms
    ];

    private BookRepository $repository;
    private array $results = [];
    private bool $allPassed = true;

    public function handle(): int
    {
        $this->repository = new BookRepository();

        $iterations = (int) $this->option('iterations');
        $warmup     = (int) $this->option('warmup');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║         PageTurner Performance Benchmark Suite           ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info("  Iterations : {$iterations}");
        $this->info("  Warmup     : {$warmup} passes");
        $this->info("  Dataset    : " . number_format(DB::table('books')->count()) . " books");
        $this->info('');

        // ── Run all benchmarks ────────────────────────────────────────────
        $this->benchmark(
            'catalog_listing',
            'Catalog Listing (100 records/page)',
            fn() => $this->repository->getActiveCatalog(100),
            $warmup,
            $iterations
        );

        $this->benchmark(
            'isbn_lookup',
            'ISBN Lookup (exact match)',
            function () {
                static $isbn = null;
                if (!$isbn) {
                    $isbn = Book::where('is_active', true)->value('isbn');
                }
                return $this->repository->findByIsbn($isbn);
            },
            $warmup,
            $iterations
        );

        $this->benchmark(
            'category_filter',
            'Category Filter (100K+ results)',
            fn() => $this->repository->getByCategory(1, 100),
            $warmup,
            $iterations
        );

        $this->benchmark(
            'fulltext_search',
            'Full-Text Search (1M records)',
            fn() => Book::search('ipsum')
                ->query(fn($q) => $q->where('is_active', true))
                ->take(100)
                ->get(),
            $warmup,
            $iterations
        );

        $this->benchmark(
            'price_range',
            'Price Range Query',
            fn() => $this->repository->getByPriceRange(10.00, 50.00, 100),
            $warmup,
            $iterations
        );

        // ── Print results table ───────────────────────────────────────────
        $this->printResultsTable();

        // ── Return exit code ──────────────────────────────────────────────
        if ($this->allPassed) {
            $this->info('');
            $this->info('✅ All benchmarks passed performance targets!');
            $this->info('');
            return self::SUCCESS;
        } else {
            $this->error('');
            $this->error('❌ Some benchmarks failed performance targets!');
            $this->error('');
            return self::FAILURE;
        }
    }

    // ─────────────────────────────────────────────
    // BENCHMARK RUNNER
    // ─────────────────────────────────────────────
    private function benchmark(
        string   $key,
        string   $label,
        callable $query,
        int      $warmup,
        int      $iterations
    ): void {
        $this->info("  ⏱  Running: {$label}");

        // Warmup pass — primes caches, JIT, connection pools
        for ($i = 0; $i < $warmup; $i++) {
            $query();
        }

        // Measurement pass
        $times = [];
        for ($i = 0; $i < $iterations; $i++) {
            $start   = hrtime(true);
            $query();
            $elapsed = (hrtime(true) - $start) / 1_000_000; // nanoseconds → ms
            $times[] = $elapsed;
        }

        $avg    = array_sum($times) / count($times);
        $min    = min($times);
        $max    = max($times);
        $total  = array_sum($times);
        $target = self::TARGETS[$key];
        $passed = $avg <= $target;

        if (!$passed) {
            $this->allPassed = false;
        }

        $this->results[$key] = [
            'label'   => $label,
            'avg'     => round($avg, 2),
            'min'     => round($min, 2),
            'max'     => round($max, 2),
            'total'   => round($total, 2),
            'target'  => $target,
            'passed'  => $passed,
        ];

        $status = $passed ? '✅' : '❌';
        $this->info("     {$status} avg: " . round($avg, 2) . "ms (target: <{$target}ms)");
    }

    // ─────────────────────────────────────────────
    // RESULTS TABLE
    // ─────────────────────────────────────────────
    private function printResultsTable(): void
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║                        Benchmark Results Summary                            ║');
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->info('');

        $rows = [];
        foreach ($this->results as $result) {
            $rows[] = [
                $result['label'],
                $result['avg'] . ' ms',
                $result['min'] . ' ms',
                $result['max'] . ' ms',
                $result['total'] . ' ms',
                '<' . $result['target'] . ' ms',
                $result['passed'] ? '✅ PASS' : '❌ FAIL',
            ];
        }

        $this->table(
            ['Query', 'Avg', 'Min', 'Max', 'Total', 'Target', 'Status'],
            $rows
        );
    }
}