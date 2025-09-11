<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration for the application
    |
    */

    // Force HTTPS connections
    'force_https' => env('FORCE_HTTPS', false),
    'https_only' => env('HTTPS_ONLY', false),

    // API Security
    'api_key_encryption_key' => env('API_KEY_ENCRYPTION_KEY'),
    
    // CORS Configuration
    'cors' => [
        'enabled' => true,
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
        'allowed_methods' => explode(',', env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,DELETE,OPTIONS')),
        'allowed_headers' => [
            'Accept',
            'Authorization',
            'Content-Type',
            'X-Requested-With',
            'X-API-Key',
            'X-CSRF-TOKEN',
        ],
        'exposed_headers' => [],
        'max_age' => 3600,
        'supports_credentials' => false,
    ],

    // IP Whitelisting for Webhooks
    'webhook_ip_whitelist' => env('WEBHOOK_IP_WHITELIST', '127.0.0.1,::1'),

    // CSRF Protection
    'csrf_protection' => [
        'enabled' => env('CSRF_PROTECTION_ENABLED', true),
        'exclude_routes' => [
            'api/*',
            'webhooks/*',
        ],
    ],

    // Rate Limiting
    'rate_limits' => [
        'api' => [
            'max_attempts' => env('API_RATE_LIMIT_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('API_RATE_LIMIT_DECAY_MINUTES', 1),
        ],
        'webhooks' => [
            'max_attempts' => env('WEBHOOK_RATE_LIMIT_MAX_ATTEMPTS', 100),
            'decay_minutes' => env('WEBHOOK_RATE_LIMIT_DECAY_MINUTES', 1),
        ],
        'admin' => [
            'max_attempts' => env('ADMIN_RATE_LIMIT_MAX_ATTEMPTS', 30),
            'decay_minutes' => env('ADMIN_RATE_LIMIT_DECAY_MINUTES', 1),
        ],
    ],

    // Security Headers
    'headers' => [
        'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'content_security_policy' => env('CSP_HEADER'),
        'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
    ],

    // Encryption
    'encryption' => [
        'key' => env('APP_KEY'),
        'cipher' => 'AES-256-CBC',
    ],

    // Session Security
    'session' => [
        'secure_cookies' => env('SESSION_SECURE_COOKIES', false),
        'http_only_cookies' => env('SESSION_HTTP_ONLY', true),
        'same_site_cookies' => env('SESSION_SAME_SITE', 'lax'),
    ],

    // File Upload Security
    'uploads' => [
        'max_file_size' => env('MAX_UPLOAD_SIZE', 2048), // KB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'text/plain',
            'text/csv',
        ],
        'scan_uploads' => env('SCAN_UPLOADS', false),
    ],

    // Database Security
    'database' => [
        'encrypt_sensitive_data' => env('DB_ENCRYPT_SENSITIVE', true),
        'log_queries' => env('DB_LOG_QUERIES', false),
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000), // ms
    ],

    // Monitoring & Alerting
    'monitoring' => [
        'failed_login_threshold' => env('FAILED_LOGIN_THRESHOLD', 5),
        'suspicious_activity_threshold' => env('SUSPICIOUS_ACTIVITY_THRESHOLD', 10),
        'alert_webhook_url' => env('SECURITY_ALERT_WEBHOOK_URL'),
        'alert_email' => env('SECURITY_ALERT_EMAIL'),
    ],

    // API Security
    'api_security' => [
        'require_api_key' => env('API_REQUIRE_KEY', true),
        'api_key_header_name' => env('API_KEY_HEADER', 'X-API-Key'),
        'rate_limit_header' => env('API_RATE_LIMIT_HEADER', true),
        'log_requests' => env('API_LOG_REQUESTS', true),
    ],

    // Webhook Security
    'webhooks' => [
        'verify_signatures' => env('WEBHOOK_VERIFY_SIGNATURES', true),
        'signature_header' => env('WEBHOOK_SIGNATURE_HEADER', 'X-Signature'),
        'timeout' => env('WEBHOOK_TIMEOUT', 30),
        'retry_attempts' => env('WEBHOOK_RETRY_ATTEMPTS', 3),
    ],
];