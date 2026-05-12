<?php

return [
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'gemini'),
    'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),

    'providers' => [
        'gemini' => [
            'api_key'  => env('GEMINI_API_KEY'),
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent',
        ],
        'ollama' => [
            'enabled'  => env('OLLAMA_ENABLED', true),
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model'    => env('OLLAMA_MODEL', 'llama3.2'),
        ],
    ],
];