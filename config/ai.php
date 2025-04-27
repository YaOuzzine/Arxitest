<?php

return [
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'temperature' => 0.7,
        ],
        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'model' => env('CLAUDE_MODEL', 'claude-3-haiku-20240307'),
            'temperature' => 0.7,
        ],
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
            'chat_url' => env('DEEPSEEK_CHAT_URL', 'https://api.deepseek.com/v1/chat/completions'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'temperature' => 0.6,
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'temperature' => 0.7,
        ],
    ],

    'max_retries' => 3,
    'timeout' => 30, // seconds
];
