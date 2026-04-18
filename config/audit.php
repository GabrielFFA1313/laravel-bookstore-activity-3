<?php

return [

    'default' => 'database',

    'drivers' => [
        'database' => [
            'table'      => 'audits',
            'connection' => null,
        ],
    ],

    'implementation' => OwenIt\Auditing\Models\Audit::class,

    'user' => [
        'morph_prefix' => 'user',
        'guards'       => ['web'],
    ],

    'resolver' => [
    'user'       => \App\Audit\UserResolver::class, // ← change this line
    'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
    'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
    'url'        => OwenIt\Auditing\Resolvers\UrlResolver::class,
],

    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    'strict' => false,

    // Fields excluded globally from all audits
    'exclude' => [
        'password',
        'password_confirmation',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verified_at',
        'updated_at',
    ],

    'empty_values'     => true,
    'allowed_empty_values' => ['key', 'old_values', 'new_values'],

    'allowed_columns'  => [],
    'timestamps'       => false,
    'threshold'        => 0,
    'console'          => false, // set true to audit CLI commands too
];