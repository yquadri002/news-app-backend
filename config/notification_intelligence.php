<?php

return [
    'breaking' => [
        'auto_push_enabled' => env('NOTIFICATION_AUTO_BREAKING', true),
        'urgency_threshold' => (float) env('NOTIFICATION_BREAKING_THRESHOLD', 15.0),
        'min_source_confirmation' => 2,
        'cooldown_minutes' => 30,
    ],

    'fatigue' => [
        'default_daily_cap' => (int) env('NOTIFICATION_DAILY_CAP', 5),
        'default_quiet_start' => '22:00',
        'default_quiet_end' => '07:00',
        'cooldown_minutes' => (int) env('NOTIFICATION_COOLDOWN_MINUTES', 60),
        'sensitivity_decay' => 0.1,
    ],

    'digest' => [
        'morning_hour' => 8,
        'afternoon_hour' => 13,
        'evening_hour' => 19,
        'articles_per_digest' => 5,
        'timezone' => env('APP_TIMEZONE', 'UTC'),
    ],

    'recommendation' => [
        'min_relevance_score' => 0.3,
        'max_recommendations_per_user' => 3,
        'recommendation_ttl_hours' => 6,
        'optimal_send_window_minutes' => 30,
    ],

    'ab_testing' => [
        'enabled' => env('NOTIFICATION_AB_TESTING', true),
        'min_sample_size' => 100,
    ],
];
