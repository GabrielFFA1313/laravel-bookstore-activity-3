<?php

// Add these seeders to your database/seeders/ folder
// Then call them from DatabaseSeeder.php

// ── MonitoringSeeder.php ─────────────────────────────────────────────────────
// Place at: database/seeders/MonitoringSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonitoringSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('role', 'admin')->value('id');

        // ── Sample import logs ────────────────────────────────────────────
        DB::table('import_logs')->insert([
            [
                'user_id'      => $adminId,
                'file_name'    => 'books_import_sample.csv',
                'model_type'   => 'App\\Models\\Book',
                'status'       => 'completed',
                'total_rows'   => 50,
                'success_rows' => 48,
                'failed_rows'  => 2,
                'failures'     => json_encode([
                    ['row' => 12, 'errors' => ['ISBN already exists']],
                    ['row' => 34, 'errors' => ['Category not found: Fiction2']],
                ]),
                'format'       => 'csv',
                'created_at'   => now()->subDays(5),
                'updated_at'   => now()->subDays(5),
            ],
            [
                'user_id'      => $adminId,
                'file_name'    => 'users_bulk_import.xlsx',
                'model_type'   => 'App\\Models\\User',
                'status'       => 'completed',
                'total_rows'   => 20,
                'success_rows' => 20,
                'failed_rows'  => 0,
                'failures'     => null,
                'format'       => 'xlsx',
                'created_at'   => now()->subDays(2),
                'updated_at'   => now()->subDays(2),
            ],
        ]);

        // ── Sample export logs ────────────────────────────────────────────
        DB::table('export_logs')->insert([
            [
                'user_id'       => $adminId,
                'model_type'    => 'App\\Models\\Order',
                'format'        => 'csv',
                'filters'       => json_encode(['status' => 'delivered', 'date_from' => '2026-01-01']),
                'columns'       => json_encode(['id', 'user', 'total', 'status', 'date']),
                'status'        => 'completed',
                'file_name'     => 'orders_export_20260401.csv',
                'download_link' => null,
                'created_at'    => now()->subDays(3),
                'updated_at'    => now()->subDays(3),
            ],
            [
                'user_id'       => $adminId,
                'model_type'    => 'App\\Models\\Book',
                'format'        => 'csv',
                'filters'       => json_encode(['category_id' => 1]),
                'columns'       => json_encode(['isbn', 'title', 'author', 'price', 'stock']),
                'status'        => 'completed',
                'file_name'     => 'books_export_mystery.csv',
                'download_link' => null,
                'created_at'    => now()->subDay(),
                'updated_at'    => now()->subDay(),
            ],
        ]);

        // ── Sample scheduled task logs ────────────────────────────────────
        DB::table('scheduled_tasks')->insert([
            [
                'command'     => 'backup:run --only-db',
                'description' => 'Daily DB backup',
                'status'      => 'completed',
                'duration_ms' => 4200,
                'output'      => 'Backup completed successfully.',
                'error'       => null,
                'started_at'  => now()->subDay()->setTime(2, 0),
                'finished_at' => now()->subDay()->setTime(2, 0, 4),
                'created_at'  => now()->subDay(),
                'updated_at'  => now()->subDay(),
            ],
            [
                'command'     => 'order:cleanup-pending',
                'description' => 'Cancel pending orders older than 24 hours',
                'status'      => 'completed',
                'duration_ms' => 320,
                'output'      => 'Cancelled 3 pending order(s).',
                'error'       => null,
                'started_at'  => now()->subHours(2),
                'finished_at' => now()->subHours(2),
                'created_at'  => now()->subHours(2),
                'updated_at'  => now()->subHours(2),
            ],
        ]);

        // ── Sample backup monitoring logs ─────────────────────────────────
        DB::table('backup_monitoring')->insert([
            [
                'user_id'          => null, // scheduled — no user
                'type'             => 'db',
                'status'           => 'success',
                'file_name'        => 'pageturner_backup_2026-04-18-02-00-00.zip',
                'file_size_bytes'  => 21760,
                'disk'             => 'local_backups',
                'error_message'    => null,
                'duration_seconds' => 4,
                'started_at'       => now()->subDay()->setTime(2, 0),
                'finished_at'      => now()->subDay()->setTime(2, 0, 4),
                'created_at'       => now()->subDay(),
                'updated_at'       => now()->subDay(),
            ],
            [
                'user_id'          => $adminId,
                'type'             => 'full',
                'status'           => 'success',
                'file_name'        => 'pageturner_backup_2026-04-13-02-30-00.zip',
                'file_size_bytes'  => 1048576,
                'disk'             => 'local_backups',
                'error_message'    => null,
                'duration_seconds' => 18,
                'started_at'       => now()->subWeek()->setTime(2, 30),
                'finished_at'      => now()->subWeek()->setTime(2, 30, 18),
                'created_at'       => now()->subWeek(),
                'updated_at'       => now()->subWeek(),
            ],
        ]);

        $this->command->info('Monitoring seed data inserted.');
    }
}