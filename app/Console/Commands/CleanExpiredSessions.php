<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanExpiredSessions extends Command
{
    protected $signature   = 'session:cleanup';
    protected $description = 'Clear expired sessions from the database';

    public function handle(): void
    {
        // Only applies if SESSION_DRIVER=database
        if (config('session.driver') !== 'database') {
            // For file-based sessions, clear old session files
            $sessionPath = config('session.files', storage_path('framework/sessions'));
            $lifetime    = config('session.lifetime', 120) * 60;
            $count       = 0;

            if (is_dir($sessionPath)) {
                foreach (glob($sessionPath . '/*') as $file) {
                    if (is_file($file) && (time() - filemtime($file)) > $lifetime) {
                        unlink($file);
                        $count++;
                    }
                }
            }

            Log::info("session:cleanup — deleted {$count} expired session file(s).");
            $this->info("Deleted {$count} expired session file(s).");
            return;
        }

        $count = DB::table('sessions')
            ->where('last_activity', '<', now()->subMinutes(config('session.lifetime', 120))->timestamp)
            ->delete();

        Log::info("session:cleanup — deleted {$count} expired session(s).");
        $this->info("Deleted {$count} expired session(s).");
    }
}