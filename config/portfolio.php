<?php

return [

    // CMS obscurity gate passcode (Part 2). May be plain or a bcrypt hash.
    'cms_passcode' => env('CMS_PASSCODE', ''),

    // Admin login lockout (Part 5): N failed attempts within the decay window.
    'login_max_attempts' => 5,
    'login_decay_seconds' => 900, // 15 minutes

    // AI assistant (Part 1) — multi-provider, switchable from Settings.
    // Default is Gemini (free tier). Each provider key/model can also be
    // overridden by an encrypted CMS Setting: {provider}_api_key / {provider}_model.
    'ai' => [
        'provider' => env('AI_PROVIDER', 'gemini'), // gemini | groq | anthropic

        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),
        ],
        'groq' => [
            'key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        ],
        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
        ],
    ],

    // ip-api.com geo lookup endpoint (Part 3).
    'geoip_endpoint' => env('GEOIP_ENDPOINT', 'http://ip-api.com/json'),

    // Self-hosted projects engine (Part 6). Path to the mysqldump binary used
    // for DB exports (XAMPP default locally; just 'mysqldump' on Linux/prod).
    'mysqldump_path' => env('MYSQLDUMP_PATH', 'D:\\xampp\\mysql\\bin\\mysqldump.exe'),

];
