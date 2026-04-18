<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RotateLogs extends Command
{
    protected $signature   = 'log:rotate';
    protected $description = 'Archive and compress old log files';

    public function handle(): void
    {
        $logPath    = storage_path('logs');
        $archivePath = storage_path('logs/archive');
        $count      = 0;

        if (!is_dir($archivePath)) {
            mkdir($archivePath, 0755, true);
        }

        // Archive log files older than 7 days
        foreach (glob($logPath . '/laravel-*.log') as $file) {
            if (is_file($file) && (time() - filemtime($file)) > (7 * 24 * 60 * 60)) {
                $filename    = basename($file);
                $destination = $archivePath . '/' . $filename . '.gz';

                // Compress using gzip
                $gz = gzopen($destination, 'wb9');
                $fh = fopen($file, 'rb');

                while (!feof($fh)) {
                    gzwrite($gz, fread($fh, 524288));
                }

                fclose($fh);
                gzclose($gz);
                unlink($file);
                $count++;
            }
        }

        // Delete archived logs older than 30 days
        $deleted = 0;
        foreach (glob($archivePath . '/*.gz') as $file) {
            if (is_file($file) && (time() - filemtime($file)) > (30 * 24 * 60 * 60)) {
                unlink($file);
                $deleted++;
            }
        }

        Log::info("log:rotate — archived {$count} log file(s), deleted {$deleted} old archive(s).");
        $this->info("Archived {$count} log file(s), deleted {$deleted} old archive(s).");
    }
}