<?php

return [
    'ad_networks' => [
        'admob' => ['name' => 'AdMob', 'default_ecpm' => 2.50],
        'meta' => ['name' => 'Meta Audience Network', 'default_ecpm' => 2.00],
        'applovin' => ['name' => 'AppLovin MAX', 'default_ecpm' => 3.00],
        'unity' => ['name' => 'Unity Ads', 'default_ecpm' => 1.80],
        'pangle' => ['name' => 'Pangle', 'default_ecpm' => 1.50],
    ],

    'optimization' => [
        'min_impressions_for_decision' => 1000,
        'frequency_cap_min' => 1,
        'frequency_cap_max' => 10,
        'ecpm_weight' => 0.4,
        'fill_rate_weight' => 0.3,
        'ctr_weight' => 0.3,
    ],

    'segmentation' => [
        'high_revenue_threshold' => 5.00,
        'low_revenue_threshold' => 0.50,
        'heavy_reader_articles' => 50,
        'casual_reader_articles' => 10,
        'ad_sensitive_ctr_threshold' => 0.01,
    ],

    'growth' => [
        'session_event_type' => 'session_end',
        'ltv_prediction_days' => 365,
    ],

    'ab_testing' => [
        'min_sample_size' => 500,
        'confidence_threshold' => 0.95,
    ],
];
