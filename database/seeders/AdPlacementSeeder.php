<?php

namespace Database\Seeders;

use App\Models\AdPlacement;
use Illuminate\Database\Seeder;

class AdPlacementSeeder extends Seeder
{
    public function run(): void
    {
        $placements = [
            [
                'name' => 'Home Feed Banner',
                'placement_key' => 'home_feed_banner',
                'format' => 'banner',
                'frequency_cap' => 3,
                'frequency_period_minutes' => 60,
                'remote_config' => ['ad_unit_id' => 'ca-app-pub-xxxxx/home-banner'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Article Interstitial',
                'placement_key' => 'article_interstitial',
                'format' => 'interstitial',
                'frequency_cap' => 1,
                'frequency_period_minutes' => 30,
                'remote_config' => ['ad_unit_id' => 'ca-app-pub-xxxxx/article-interstitial'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Article Native Ad',
                'placement_key' => 'article_native',
                'format' => 'native',
                'frequency_cap' => 5,
                'frequency_period_minutes' => 60,
                'remote_config' => ['ad_unit_id' => 'ca-app-pub-xxxxx/article-native'],
                'sort_order' => 3,
            ],
        ];

        foreach ($placements as $placement) {
            AdPlacement::updateOrCreate(
                ['placement_key' => $placement['placement_key']],
                array_merge($placement, ['is_enabled' => true])
            );
        }
    }
}
