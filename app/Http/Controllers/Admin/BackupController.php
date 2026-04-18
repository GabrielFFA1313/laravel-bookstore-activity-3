<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackupFiles();
        return view('admin.backup.index', compact('backups'));
    }

 public function run(Request $request)
{
    $type = $request->input('type', 'db');

    try {
        $pgPath    = 'C:/Program Files/PostgreSQL/18/bin';
        $phpPath   = dirname(PHP_BINARY);
        $artisan   = base_path('artisan');
        $command   = $type === 'full' ? 'backup:run' : 'backup:run --only-db';

        $fullCommand = "SET PATH={$pgPath};{$phpPath};%PATH% && php \"{$artisan}\" {$command} 2>&1";

        $output = shell_exec("cmd /c \"{$fullCommand}\"");

        \Illuminate\Support\Facades\Log::info('Backup output: ' . $output);

        if (str_contains($output, 'failed') || str_contains($output, 'error')) {
            return back()->with('error', 'Backup failed: ' . $output);
        }

        return back()->with('success', ucfirst($type) . ' backup completed successfully!');

    } catch (\Exception $e) {
        return back()->with('error', 'Backup failed: ' . $e->getMessage());
    }
}
    public function clean()
    {
        try {
            Artisan::call('backup:clean');
            return back()->with('success', 'Old backups cleaned up successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cleanup failed: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
{
    $disk = Storage::disk('local_backups');
    
    // Search for the file recursively since it's in a subfolder
    $allFiles = $disk->allFiles();
    $found    = null;

    foreach ($allFiles as $file) {
        if (basename($file) === $filename) {
            $found = $file;
            break;
        }
    }

    if (!$found) {
        abort(404, 'Backup file not found.');
    }

    return $disk->download($found, $filename);
}

  private function getBackupFiles(): array
{
    $disk  = Storage::disk('local_backups');
    $files = [];

    try {
        $allFiles = $disk->allFiles();

        foreach ($allFiles as $file) {
            $files[] = [
                'name'     => basename($file),
                'size'     => $this->formatBytes($disk->size($file)),
                'modified' => date('M d, Y g:i A', $disk->lastModified($file)),
                'path'     => $file,
            ];
        }

        usort($files, fn($a, $b) => strcmp($b['modified'], $a['modified']));

    } catch (\Exception $e) {
        // No backups yet
    }

    return $files;
}
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }
}