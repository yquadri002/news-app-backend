<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\RssSource;
use Illuminate\Database\Seeder;

class RssSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['name' => 'BBC World News', 'url' => 'https://feeds.bbci.co.uk/news/world/rss.xml', 'category' => 'world', 'priority' => 10],
            ['name' => 'TechCrunch', 'url' => 'https://techcrunch.com/feed/', 'category' => 'technology', 'priority' => 8],
            ['name' => 'NPR News', 'url' => 'https://feeds.npr.org/1001/rss.xml', 'category' => 'world', 'priority' => 9],
        ];

        foreach ($sources as $source) {
            $category = Category::where('slug', $source['category'])->first();

            RssSource::updateOrCreate(
                ['url' => $source['url']],
                [
                    'name' => $source['name'],
                    'category_id' => $category?->id,
                    'priority' => $source['priority'],
                    'is_active' => true,
                    'fetch_interval_minutes' => 15,
                ]
            );
        }
    }
}
