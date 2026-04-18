<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SendDailySalesReport;

// ── BACKUP SCHEDULE ──────────────────────────────────────────────────────────

// Daily backup at 2:00 AM every day
Schedule::command('backup:run --only-db')
    ->dailyAt('02:00')
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Daily DB backup failed at ' . now());
    })
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Daily DB backup completed at ' . now());
    });

// Full backup every Sunday at 2:30 AM (DB + files)
Schedule::command('backup:run')
    ->weeklyOn(0, '02:30') // 0 = Sunday
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Weekly full backup failed at ' . now());
    });

// Clean old backups daily (enforces retention policy)
Schedule::command('backup:clean')->dailyAt('03:00');

// Monitor backup health daily
Schedule::command('backup:monitor')->dailyAt('03:30');

// ── REPORTS ──────────────────────────────────────────────────────────────────

// Daily sales report emailed to admins every morning
Schedule::command(SendDailySalesReport::class)->dailyAt('08:00');