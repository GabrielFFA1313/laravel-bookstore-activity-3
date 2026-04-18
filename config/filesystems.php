<?php

// Add this disk inside the 'disks' array in config/filesystems.php
// alongside the existing 'local', 'public', and 's3' disks:

/*
'local_backups' => [
    'driver' => 'local',
    'root'   => storage_path('app/backups'),
],
*/

// Your full filesystems.php disks section should look like:

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        // ── ADD THIS ─────────────────────────────────────────
        'local_backups' => [
            'driver' => 'local',
            'root'   => storage_path('app/backups'),
        ],
        // ─────────────────────────────────────────────────────

        's3' => [
            'driver'   => 's3',
            'key'      => env('AWS_ACCESS_KEY_ID'),
            'secret'   => env('AWS_SECRET_ACCESS_KEY'),
            'region'   => env('AWS_DEFAULT_REGION'),
            'bucket'   => env('AWS_BUCKET'),
            'url'      => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw'    => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];