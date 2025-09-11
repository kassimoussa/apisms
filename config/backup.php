<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Backup Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for automated database backups
    |
    */

    // Storage disk to use for backups
    'disk' => env('DB_BACKUP_STORAGE_DISK', 'local'),
    
    // Enable/disable backup functionality
    'enabled' => env('DB_BACKUP_ENABLED', true),
    
    // Number of days to retain backups
    'retention_days' => env('DB_BACKUP_RETENTION_DAYS', 30),
    
    // Enable compression for backup files
    'compression' => env('DB_BACKUP_COMPRESSION', true),
    
    // Backup schedule (cron format)
    'schedule' => env('DB_BACKUP_SCHEDULE', '0 2 * * *'), // Daily at 2 AM
    
    // Tables to exclude from backup
    'exclude_tables' => [
        'sessions',
        'cache',
        'cache_locks',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
    ],
    
    // Tables to skip data (structure only)
    'skip_data_tables' => [
        'sessions',
        'cache',
        'failed_jobs',
        'job_batches',
    ],
    
    // Notification settings
    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', true),
        
        'channels' => [
            'mail' => [
                'enabled' => env('BACKUP_MAIL_NOTIFICATIONS', true),
                'to' => explode(',', env('BACKUP_MAIL_TO', 'admin@example.com')),
            ],
            
            'slack' => [
                'enabled' => env('BACKUP_SLACK_NOTIFICATIONS', false),
                'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL'),
                'channel' => env('BACKUP_SLACK_CHANNEL', '#alerts'),
            ],
        ],
        
        'notify_on' => [
            'success' => env('BACKUP_NOTIFY_SUCCESS', false),
            'failure' => env('BACKUP_NOTIFY_FAILURE', true),
            'cleanup' => env('BACKUP_NOTIFY_CLEANUP', false),
        ],
    ],
    
    // Health check settings
    'health_checks' => [
        'enabled' => env('BACKUP_HEALTH_CHECKS', true),
        
        'max_age_hours' => env('BACKUP_MAX_AGE_HOURS', 48),
        'min_size_mb' => env('BACKUP_MIN_SIZE_MB', 1),
        'max_size_mb' => env('BACKUP_MAX_SIZE_MB', 1000),
    ],
    
    // Cloud storage settings
    'cloud' => [
        's3' => [
            'enabled' => env('BACKUP_S3_ENABLED', false),
            'bucket' => env('AWS_BUCKET'),
            'path' => env('BACKUP_S3_PATH', 'database-backups'),
            'storage_class' => env('BACKUP_S3_STORAGE_CLASS', 'STANDARD_IA'),
        ],
        
        'sync_to_cloud' => env('BACKUP_SYNC_TO_CLOUD', false),
        'keep_local_copy' => env('BACKUP_KEEP_LOCAL_COPY', true),
    ],
];