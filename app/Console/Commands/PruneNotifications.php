<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneNotifications extends Command
{
    protected $signature   = 'notification:prune';
    protected $description = 'Delete old notification records older than 90 days';

    public function handle(): void
    {
        $count = DB::table('notifications')
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        Log::info("notification:prune — deleted {$count} old notification(s).");
        $this->info("Deleted {$count} old notification(s).");
    }
}