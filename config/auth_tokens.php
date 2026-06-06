<?php

return [
    'access_ttl_minutes' => (int) env('SANCTUM_ACCESS_TOKEN_TTL', 60),
    'refresh_ttl_days' => (int) env('SANCTUM_REFRESH_TOKEN_TTL', 30),
    'abilities' => [
        'access' => 'access',
        'refresh' => 'refresh',
    ],
];
