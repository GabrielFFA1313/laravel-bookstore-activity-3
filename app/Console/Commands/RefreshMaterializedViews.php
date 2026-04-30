<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshMaterializedViews extends Command
{
    protected $signature   = 'app:refresh-materialized-views';
    protected $description = 'Refresh all materialized views for reporting';

    public function handle(): void
    {
        $this->info('🔄 Refreshing materialized views...');

        $views = [
            'mv_bestseller_stats',
            'mv_inventory_summary',
            'mv_format_distribution',
        ];

        $startTime = microtime(true);

        foreach ($views as $view) {
            $viewStart = microtime(true);
            DB::unprepared("REFRESH MATERIALIZED VIEW CONCURRENTLY {$view};");
            $elapsed = round(microtime(true) - $viewStart, 2);
            $this->info("  ✅ {$view} refreshed in {$elapsed}s");
        }

        $total = round(microtime(true) - $startTime, 2);
        $this->info("✅ All views refreshed in {$total}s");
    }
}