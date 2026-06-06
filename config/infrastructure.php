<?php

return [
    'monitoring' => [
        'horizon_enabled' => env('HORIZON_ENABLED', false),
        'pulse_enabled' => env('PULSE_ENABLED', false),
        'telescope_enabled' => env('TELESCOPE_ENABLED', false),
        'allowed_emails' => env('MONITORING_ALLOWED_EMAILS', 'admin@newshub.pro'),
    ],

    'device_registration' => [
        'per_minute' => (int) env('DEVICE_REGISTER_PER_MINUTE', 5),
        'per_day' => (int) env('DEVICE_REGISTER_PER_DAY', 20),
    ],

    'health' => [
        'check_database' => env('HEALTH_CHECK_DATABASE', true),
        'check_redis' => env('HEALTH_CHECK_REDIS', true),
        'check_queue' => env('HEALTH_CHECK_QUEUE', true),
        'check_storage' => env('HEALTH_CHECK_STORAGE', true),
    ],

    'alerting' => [
        'enabled' => env('ALERTING_ENABLED', false),
        'slack_webhook' => env('ALERT_SLACK_WEBHOOK'),
        'email' => env('ALERT_EMAIL'),
        'thresholds' => [
            'cpu_percent' => (int) env('ALERT_CPU_THRESHOLD', 85),
            'memory_percent' => (int) env('ALERT_MEMORY_THRESHOLD', 85),
            'queue_backlog' => (int) env('ALERT_QUEUE_BACKLOG', 1000),
            'db_connections_percent' => (int) env('ALERT_DB_CONNECTIONS', 80),
            'notification_failure_rate' => (float) env('ALERT_NOTIFICATION_FAILURE_RATE', 0.1),
        ],
    ],

    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'disk' => env('BACKUP_DISK', 's3-backup'),
        'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
        'schedule' => env('BACKUP_SCHEDULE', 'daily'),
    ],

    'cdn' => [
        'enabled' => env('CDN_ENABLED', false),
        'url' => env('CDN_URL'),
        'signed_url_ttl' => (int) env('CDN_SIGNED_URL_TTL', 3600),
    ],

    'rate_limiting' => [
        'api_per_minute' => (int) env('API_RATE_LIMIT', 120),
        'auth_per_minute' => (int) env('AUTH_RATE_LIMIT', 10),
        'public_per_minute' => (int) env('PUBLIC_RATE_LIMIT', 300),
    ],

    'scaling' => [
        'target_dau' => 100000,
        'target_mau' => 1000000,
        'horizon_workers_max' => (int) env('HORIZON_MAX_PROCESSES', 30),
    ],
];
