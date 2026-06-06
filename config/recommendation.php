<?php

return [
    'ranking_weights' => [
        'user_interest' => (float) env('RECOMMEND_USER_INTEREST_WEIGHT', 0.30),
        'trending' => (float) env('RECOMMEND_TRENDING_WEIGHT', 0.20),
        'breaking' => (float) env('RECOMMEND_BREAKING_WEIGHT', 0.10),
        'freshness' => (float) env('RECOMMEND_FRESHNESS_WEIGHT', 0.15),
        'engagement' => (float) env('RECOMMEND_ENGAGEMENT_WEIGHT', 0.15),
        'source_quality' => (float) env('RECOMMEND_SOURCE_QUALITY_WEIGHT', 0.10),
    ],

    'cold_start' => [
        'min_events_for_warm' => (int) env('RECOMMEND_COLD_START_THRESHOLD', 10),
        'popular_category_limit' => 5,
        'default_segment' => 'general',
    ],

    'interest_decay_days' => (int) env('RECOMMEND_INTEREST_DECAY_DAYS', 30),

    'feed' => [
        'default_per_page' => 20,
        'max_candidates' => 200,
        'diversity_penalty' => 0.15,
        'seen_article_penalty' => 0.5,
    ],

    'segments' => [
        'politics-readers' => ['politics', 'government', 'election'],
        'sports-readers' => ['sports', 'football', 'cricket', 'match'],
        'technology-readers' => ['technology', 'tech', 'software', 'ai'],
        'business-readers' => ['business', 'economy', 'market', 'finance'],
        'entertainment-readers' => ['entertainment', 'movie', 'music', 'celebrity'],
    ],
];
