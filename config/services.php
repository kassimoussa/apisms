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

    'kannel' => [
        'url' => env('KANNEL_URL', 'http://localhost:13013/cgi-bin/sendsms'),
        'username' => env('KANNEL_USERNAME'),
        'password' => env('KANNEL_PASSWORD'),
        'from' => env('KANNEL_FROM', '+253XXXXXXXX'),
        'timeout' => env('KANNEL_TIMEOUT', 30),
    ],

    'sms' => [
        'default_country_code' => env('SMS_DEFAULT_COUNTRY_CODE', '+253'),
        'rate_limit_per_minute' => env('SMS_RATE_LIMIT_PER_MINUTE', 60),
        'max_length' => env('SMS_MAX_LENGTH', 160),
    ],

];
