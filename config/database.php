<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        // ── PRIMARY PostgreSQL connection with read/write splitting ────────
        'pgsql' => [
        // ── Read replicas — offload heavy reporting queries ──────────────
        'read' => [
            'host' => [
                env('DB_READ_HOST_1', env('DB_HOST', '127.0.0.1')),
                env('DB_READ_HOST_2', env('DB_HOST', '127.0.0.1')),
            ],
        ],

        // ── Write master — all INSERT/UPDATE/DELETE go here ───────────────
        'write' => [
            'host' => [
                env('DB_HOST', '127.0.0.1'),
            ],
        ],

        // ── Sticky: ensures read-after-write consistency ──────────────────
        // If you write in this request, subsequent reads use write connection
        'sticky'    => true,

        'driver'    => 'pgsql',
        'url'       => env('DATABASE_URL'),
        'port'      => env('DB_PORT', '5432'),
        'database'  => env('DB_DATABASE', 'laravel'),
        'username'  => env('DB_USERNAME', 'postgres'),
        'password'  => env('DB_PASSWORD', ''),
        'charset'   => 'utf8',
        'prefix'    => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode'   => 'prefer',
    ],
        // ── Connection pooling for high-concurrency (Swoole/RoadRunner) ───
    'options'  => [
        \PDO::ATTR_PERSISTENT      => true,   // Reuse connections
        \PDO::ATTR_EMULATE_PREPARES => true,  // Better compatibility
    ],

        // ── SQLite (kept for testing) ──────────────────────────────────────
        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DB_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver'         => 'mysql',
            'url'            => env('DB_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'laravel'),
            'username'       => env('DB_USERNAME', 'root'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => env('DB_CHARSET', 'utf8mb4'),
            'collation'      => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
        ],

    ],

    'migrations' => [
        'table'  => 'migrations',
        'update_date_on_publish' => true,
    ],

'redis' => [

    'client' => env('REDIS_CLIENT', 'predis'),

    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix'  => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
    ],

    // Database 0 — General purpose (default Laravel usage)
    'default' => [
        'url'      => env('REDIS_URL'),
        'host'     => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port'     => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],

    // Database 1 — Query result caching
    'cache' => [
        'url'      => env('REDIS_URL'),
        'host'     => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port'     => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],

    // Database 2 — Session storage
    'session' => [
        'url'      => env('REDIS_URL'),
        'host'     => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port'     => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_SESSION_DB', '2'),
    ],

],

];