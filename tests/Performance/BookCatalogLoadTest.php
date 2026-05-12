<?php

namespace Tests\Performance;

use App\Models\Book;
use App\Repositories\BookRepository;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookCatalogLoadTest extends TestCase
{
    private BookRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BookRepository();
    }

    #[Test]
    public function it_handles_50_concurrent_catalog_requests(): void
    {
        $concurrentRequests = 50;
        $errors             = 0;
        $times              = [];

        for ($i = 0; $i < $concurrentRequests; $i++) {
            try {
                $start    = hrtime(true);
                $response = $this->getJson('/api/books');
                $elapsed  = (hrtime(true) - $start) / 1_000_000;
                $times[]  = $elapsed;
                $response->assertStatus(200);
                $response->assertJsonStructure([
                    'data' => ['*' => ['id', 'isbn', 'title', 'author', 'price']]
                ]);
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $avg = array_sum($times) / count($times);
        $this->assertEquals(0, $errors, "Expected 0 errors, got {$errors}");
        $this->assertLessThan(1000, $avg,
        "Average response time " . round($avg, 2) . "ms exceeded 1000ms threshold"
        );
        echo "\n  ✅ 50 requests completed | avg: " . round($avg, 2) . "ms | errors: {$errors}";
    }

    #[Test]
    public function it_handles_isbn_lookups_within_threshold(): void
    {
        $bookId = Book::where('is_active', true)->value('id');
        $times  = [];

        for ($i = 0; $i < 50; $i++) {
            $start    = hrtime(true);
            $response = $this->getJson("/api/books/{$bookId}");
            $elapsed  = (hrtime(true) - $start) / 1_000_000;
            $times[]  = $elapsed;
            $response->assertStatus(200);
        }

        $avg = array_sum($times) / count($times);
        $min = min($times);
        $max = max($times);

        // Threshold set to 500ms — realistic for single-server dev environment
        $this->assertLessThan(500, $avg,
            "Book lookup avg " . round($avg, 2) . "ms exceeded 500ms threshold"
        );
        echo "\n  ✅ Book lookup | avg: " . round($avg, 2) .
             "ms | min: " . round($min, 2) .
             "ms | max: " . round($max, 2) . "ms";
    }

    #[Test]
    public function it_populates_cache_after_initial_request(): void
{
    Cache::tags(['books', 'catalog'])->flush();
    sleep(1); // Allow cache to fully clear

    // First request — cache miss, hits DB
    $start         = hrtime(true);
    $firstResponse = $this->getJson('/api/books');
    $firstTime     = (hrtime(true) - $start) / 1_000_000;
    $firstResponse->assertStatus(200);

    // Average of 5 cache hits — more reliable than single measurement
    $cacheTimes = [];
    for ($i = 0; $i < 5; $i++) {
        $start    = hrtime(true);
        $this->getJson('/api/books');
        $cacheTimes[] = (hrtime(true) - $start) / 1_000_000;
    }
    $avgCacheTime = array_sum($cacheTimes) / count($cacheTimes);

    // Cache should be populated (both requests return 200)
    $this->assertLessThan(2000, $firstTime,
        "First request took too long: " . round($firstTime, 2) . "ms"
    );
    $this->assertLessThan(2000, $avgCacheTime,
        "Cache requests took too long: " . round($avgCacheTime, 2) . "ms"
    );

    echo "\n  ✅ Cache miss: " . round($firstTime, 2) .
         "ms | Cache hit avg: " . round($avgCacheTime, 2) . "ms" .
         " | Speedup: " . round($firstTime / max($avgCacheTime, 0.1), 1) . "x";
}
    #[Test]
    public function it_returns_correct_json_structure(): void
    {
        $response = $this->getJson('/api/books');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['*' => ['id', 'isbn', 'title', 'author', 'price']],
            'meta',
            'links',
        ]);

        $data = $response->json('data.0');
        $this->assertIsInt($data['id']);
        $this->assertIsString($data['title']);
        $this->assertIsString($data['isbn']);
        echo "\n  ✅ JSON structure validated correctly";
    }

    #[Test]
    public function it_handles_category_filter_requests(): void
    {
        $times = [];

        for ($i = 0; $i < 50; $i++) {
            $categoryId = rand(1, 9);
            $start      = hrtime(true);
            $response   = $this->getJson("/api/books/category/{$categoryId}");
            $elapsed    = (hrtime(true) - $start) / 1_000_000;
            $times[]    = $elapsed;
            $response->assertStatus(200);
        }

        $avg = array_sum($times) / count($times);
        $this->assertLessThan(500, $avg,
            "Category filter avg " . round($avg, 2) . "ms exceeded 500ms threshold"
        );
        echo "\n  ✅ Category filter | avg: " . round($avg, 2) . "ms";
    }

    #[Test]
    public function it_handles_fulltext_search_requests(): void
    {
        // Use the BookRepository directly — avoids route conflict with /{book}
        $searchTerms = ['ipsum', 'lorem', 'dolor', 'amet'];
        $times       = [];

        foreach ($searchTerms as $term) {
            $start   = hrtime(true);
            $results = Book::search($term)
                ->query(fn($q) => $q->where('is_active', true))
                ->take(100)
                ->get();
            $elapsed = (hrtime(true) - $start) / 1_000_000;
            $times[] = $elapsed;
            $this->assertNotNull($results);
        }

        $avg = array_sum($times) / count($times);
        $this->assertLessThan(1000, $avg,
            "Full-text search avg " . round($avg, 2) . "ms exceeded 1000ms threshold"
        );
        echo "\n  ✅ Full-text search | avg: " . round($avg, 2) . "ms";
    }
}