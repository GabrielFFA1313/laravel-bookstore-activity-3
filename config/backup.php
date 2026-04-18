<?php

return [

    'backup' => [

        'name' => env('APP_NAME', 'pageturner'),

        'database_dump_binary_path' => 'C:/Program Files/PostgreSQL/18/bin/',

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('framework'),
                    storage_path('logs'),
                    base_path('.git'),
                ],
                'follow_links'                    => false,
                'ignore_unreadable_directories'   => false,
                'relative_path'                   => null,
            ],

            'databases' => [
                'pgsql',
            ],
        ],

        'database_dump_compressor'      => null,
        'database_dump_file_extension'  => '',

      'destination' => [
    'filename_prefix' => 'pageturner_backup_',
    'disks'           => [
        'local_backups',
        's3',
        ],
    ],

        'temporary_directory' => storage_path('app/backup-temp'),
        'password'            => env('BACKUP_ARCHIVE_PASSWORD', null),
        'encryption'          => 'default',
        'tries'               => 1,
        'retry_delay'         => 0,
    ],

   'notifications' => [

    'notifications' => [
        \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class         => ['mail'],
        \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
        \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class        => ['mail'],
        \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class     => ['mail'],
        \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class   => [],
        \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class    => [],
    ],

    'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

    'mail' => [
        'to'   => env('BACKUP_NOTIFY_EMAIL', 'firedicecreeper@gmail.com'),
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'firedicecreeper@gmail.com'),
            'name'    => env('MAIL_FROM_NAME', 'PageTurner Backup'),
        ],
    ],

    'slack' => [
        'webhook_url' => '',
        'channel'     => null,
        'username'    => null,
        'icon'        => null,
    ],

    'discord' => [
        'webhook_url' => '',
        'username'    => '',
        'avatar_url'  => '',
    ],

],

    'monitor_backups' => [
        [
            'name'          => env('APP_NAME', 'pageturner'),
            'disks'         => ['local_backups'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class          => 2,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
            'when_backups_have_not_been_created_for_days'          => 1,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
    'keep_all_backups_for_days'                            => 0,
    'keep_daily_backups_for_days'                          => 1,
    'keep_weekly_backups_for_weeks'                        => 1,
    'keep_monthly_backups_for_months'                      => 1,
    'keep_yearly_backups_for_years'                        => 1,
    'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
],

        'tries'       => 1,
        'retry_delay' => 0,
    ],

];