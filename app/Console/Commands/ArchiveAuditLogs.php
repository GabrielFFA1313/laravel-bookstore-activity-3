<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveAuditLogs extends Command
{
    protected $signature   = 'audit:archive';
    protected $description = 'Archive audit logs older than 1 year';

    public function handle(): void
    {
        // Archive old import/export logs
        $count = \App\Models\ImportExportLog::where('created_at', '<', now()->subYear())
            ->delete();

        Log::info("audit:archive — archived/deleted {$count} old audit log(s).");
        $this->info("Archived {$count} old audit log(s).");
    }
}