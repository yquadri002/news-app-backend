<?php

return [
    'queues' => [
        'rss' => env('RSS_QUEUE', 'rss'),
        'ingestion' => env('INGESTION_QUEUE', 'ingestion'),
    ],

    'fetch' => [
        'timeout_seconds' => env('RSS_FETCH_TIMEOUT', 20),
        'max_retries' => env('RSS_FETCH_MAX_RETRIES', 3),
        'user_agent' => 'NewsHubPro/1.0 RSS Aggregator',
    ],

    'duplicate_detection' => [
        'title_similarity_threshold' => 85,
        'content_similarity_threshold' => 80,
        'lookback_hours' => 72,
    ],

    'breaking_news' => [
        'score_threshold' => 15.0,
    ],

    'trending' => [
        'time_decay_half_life_hours' => 12,
        'lookback_days' => 7,
    ],

    'auto_approve' => env('INGESTION_AUTO_APPROVE', true),
];
