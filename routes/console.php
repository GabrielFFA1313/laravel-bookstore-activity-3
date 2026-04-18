<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Console\Commands\SendDailySalesReport;
use App\Console\Commands\CancelPendingOrders;
use App\Console\Commands\CleanExpiredSessions;
use App\Console\Commands\RotateLogs;
use App\Console\Commands\PruneNotifications;
use App\Console\Commands\ArchiveAuditLogs;

// ── BACKUPS ───────────────────────────────────────────────────────────────────

// Daily DB backup at 2:00 AM
Schedule::command('backup:run --only-db')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('backup:run — daily DB backup completed at ' . now()))
    ->onFailure(fn() => Log::error('backup:run — daily DB backup FAILED at ' . now()));

// Full backup every Sunday at 2:30 AM
Schedule::command('backup:run')
    ->weeklyOn(0, '02:30')
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('backup:run — weekly full backup completed at ' . now()))
    ->onFailure(fn() => Log::error('backup:run — weekly full backup FAILED at ' . now()));

// Clean old backups daily at 3:00 AM
Schedule::command('backup:clean')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('backup:clean — completed at ' . now()))
    ->onFailure(fn() => Log::error('backup:clean — FAILED at ' . now()));

// Monitor backup health daily at 3:30 AM
Schedule::command('backup:monitor')
    ->dailyAt('03:30')
    ->onFailure(fn() => Log::error('backup:monitor — FAILED at ' . now()));

// ── ORDERS ────────────────────────────────────────────────────────────────────

// Cancel pending orders older than 24 hours — runs every hour
Schedule::command(CancelPendingOrders::class)
    ->hourly()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('order:cleanup-pending — completed at ' . now()))
    ->onFailure(fn() => Log::error('order:cleanup-pending — FAILED at ' . now()));

// ── SESSIONS ──────────────────────────────────────────────────────────────────

// Clear expired sessions daily
Schedule::command(CleanExpiredSessions::class)
    ->daily()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('session:cleanup — completed at ' . now()))
    ->onFailure(fn() => Log::error('session:cleanup — FAILED at ' . now()));

// ── LOGS ──────────────────────────────────────────────────────────────────────

// Rotate and compress old logs weekly
Schedule::command(RotateLogs::class)
    ->weekly()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('log:rotate — completed at ' . now()))
    ->onFailure(fn() => Log::error('log:rotate — FAILED at ' . now()));

// ── REPORTS ───────────────────────────────────────────────────────────────────

// Generate and email daily sales report at 6:00 AM
Schedule::command(SendDailySalesReport::class)
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('report:generate-daily — completed at ' . now()))
    ->onFailure(fn() => Log::error('report:generate-daily — FAILED at ' . now()));

// ── NOTIFICATIONS ─────────────────────────────────────────────────────────────

// Prune old notifications weekly
Schedule::command(PruneNotifications::class)
    ->weekly()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('notification:prune — completed at ' . now()))
    ->onFailure(fn() => Log::error('notification:prune — FAILED at ' . now()));

// ── AUDIT LOGS ────────────────────────────────────────────────────────────────

// Archive old audit logs monthly
Schedule::command(ArchiveAuditLogs::class)
    ->monthly()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('audit:archive — completed at ' . now()))
    ->onFailure(fn() => Log::error('audit:archive — FAILED at ' . now()));