<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'chat_url' => env('DEEPSEEK_CHAT_URL', 'https://api.deepseek.com/chat/completions'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'uri'   => env('OPENAI_API_URI', 'https://api.openai.com/v1'),
    ],

    'atlassian' => [
        'client_id' => env('ATLASSIAN_CLIENT_ID'),
        'client_secret' => env('ATLASSIAN_CLIENT_SECRET'),
        'redirect' => env('ATLASSIAN_OAUTH_CALLBACK'),
        'base_uri'      => env('ATLASSIAN_BASE_URI', 'https://auth.atlassian.com'),
        'api_uri'       => env('ATLASSIAN_API_URI', 'https://api.atlassian.com'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'github' => [
        'client_id' => env('GITHUB_OAUTH_CLIENT_ID'),
        'client_secret' => env('GITHUB_OAUTH_CLIENT_SECRET'),
        'redirect' => env('GITHUB_OAUTH_CALLBACK_URL'),
        'api_uri'       => env('GITHUB_API_URI', 'https://api.github.com'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_OAUTH_CLIENT_ID'),
        'client_secret' => env('GOOGLE_OAUTH_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_OAUTH_CALLBACK_URL'),
    ],
    'microsoft' => [
        'client_id' => env('MICROSOFT_OAUTH_CLIENT_VALUE'),
        'client_secret' => env('MICROSOFT_OAUTH_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_OAUTH_CALLBACK_URL'),
    ],
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'phone_number' => env('TWILIO_PHONE_NUMBER'),
    ],

];
