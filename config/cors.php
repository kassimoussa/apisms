<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => explode(',', env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS')),

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    'allowed_origins_patterns' => explode(',', env('CORS_ALLOWED_ORIGIN_PATTERNS', '')),

    'allowed_headers' => explode(',', env('CORS_ALLOWED_HEADERS', 'Accept,Authorization,Content-Type,X-Requested-With,X-API-Key,X-CSRF-TOKEN')),

    'exposed_headers' => explode(',', env('CORS_EXPOSED_HEADERS', 'X-Rate-Limit-Remaining,X-Rate-Limit-Limit,X-Rate-Limit-Reset')),

    'max_age' => env('CORS_MAX_AGE', 3600),

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

];