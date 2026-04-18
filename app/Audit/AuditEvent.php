<?php

namespace App\Audit;

use App\Models\User;
use App\Notifications\CriticalSecurityEventNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditEvent
{
    // Critical events that trigger immediate admin email alerts
    protected static array $criticalEvents = [
        'login_failed',
        '2fa_disabled',
        'password_changed',
        'role_changed',
        'permission_changed',
        'backup_failed',
    ];

    /**
     * Log a custom audit event manually.
     */
    public static function log(
    string $event,
    string $auditableType,
    int|string|null $auditableId = 0,
    array $oldValues = [],
    array $newValues = [],
    array $metadata = []
): void {
    $request = request();
    $ip      = $request?->ip() ?? '127.0.0.1';

    $data = [
        // ← remove the 'id' line entirely
        'user_type'      => Auth::check() ? get_class(Auth::user()) : null,
        'user_id'        => Auth::id(),
        'event'          => $event,
        'auditable_type' => $auditableType,
        'auditable_id'   => $auditableId,
        'old_values'     => json_encode($oldValues),
        'new_values'     => json_encode($newValues),
        'url'            => $request?->fullUrl(),
        'ip_address'     => $ip,
        'user_agent'     => $request?->userAgent(),
        'tags'           => $metadata['tags'] ?? null,
        'created_at'     => now(),
        'updated_at'     => now(),
    ];

    $data['checksum'] = hash('sha256', json_encode([
        $data['event'],
        $data['auditable_type'],
        $data['auditable_id'],
        $data['old_values'],
        $data['new_values'],
        $data['ip_address'],
        $data['created_at'],
    ]));

    DB::table('audits')->insert($data);

    if (in_array($event, self::$criticalEvents)) {
        self::alertAdmins($event, $oldValues + $newValues + $metadata, $ip);
    }
}
    /**
     * Send email alert to all admins for critical security events.
     */
    protected static function alertAdmins(string $event, array $details, string $ip): void
    {
        $performedBy = Auth::check()
            ? Auth::user()->name . ' (' . Auth::user()->email . ')'
            : 'Unauthenticated';

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new CriticalSecurityEventNotification(
                $event,
                $details,
                $performedBy,
                $ip
            ));
        }
    }

    // ── Convenience methods ──────────────────────────────────────────────────

    public static function login(int $userId): void
    {
        self::log('login', 'App\\Models\\User', $userId, [], [], ['tags' => 'auth']);
    }

    public static function logout(int $userId): void
    {
        self::log('logout', 'App\\Models\\User', $userId, [], [], ['tags' => 'auth']);
    }

    public static function loginFailed(string $email): void
{
    self::log('login_failed', 'App\\Models\\User', 0, [], ['email' => $email], ['tags' => 'auth,security']);
}

    public static function passwordChanged(int $userId): void
    {
        self::log('password_changed', 'App\\Models\\User', $userId, [], [], ['tags' => 'auth,security']);
    }

    public static function twoFactorEnabled(int $userId, string $type): void
    {
        self::log('2fa_enabled', 'App\\Models\\User', $userId, [], ['type' => $type], ['tags' => 'auth,security']);
    }

    public static function twoFactorDisabled(int $userId): void
    {
        self::log('2fa_disabled', 'App\\Models\\User', $userId, [], [], ['tags' => 'auth,security']);
    }

    public static function roleChanged(int $userId, string $from, string $to): void
    {
        self::log('role_changed', 'App\\Models\\User', $userId,
            ['role' => $from],
            ['role' => $to],
            ['tags' => 'security,permission']
        );
    }

    public static function orderStatusChanged(int $orderId, string $from, string $to): void
    {
        self::log('status_changed', 'App\\Models\\Order', $orderId,
            ['status' => $from],
            ['status' => $to],
            ['tags' => 'order']
        );
    }

    public static function backupRun(string $type, bool $success): void
    {
        self::log($success ? 'backup_success' : 'backup_failed',
            'System\\Backup', 0,
            [],
            ['type' => $type, 'success' => $success],
            ['tags' => 'system,backup']
        );
    }

    public static function importRun(string $model, int $success, int $failed): void
    {
        self::log('import_completed', $model, 0,
            [],
            ['success_rows' => $success, 'failed_rows' => $failed],
            ['tags' => 'system,import']
        );
    }

    public static function exportRun(string $model, array $filters): void
    {
        self::log('export_completed', $model, 0,
            [],
            ['filters' => $filters],
            ['tags' => 'system,export']
        );
    }
}