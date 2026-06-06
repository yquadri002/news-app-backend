<?php

namespace Database\Seeders;

use App\Models\UserSegment;
use Illuminate\Database\Seeder;

class UserSegmentSeeder extends Seeder
{
    public function run(): void
    {
        $segments = [
            [
                'name' => 'Politics Readers',
                'slug' => 'politics-readers',
                'description' => 'Users who frequently read political news',
                'criteria' => ['keywords' => ['politics', 'government', 'election', 'parliament']],
            ],
            [
                'name' => 'Sports Readers',
                'slug' => 'sports-readers',
                'description' => 'Users who frequently read sports news',
                'criteria' => ['keywords' => ['sports', 'football', 'cricket', 'match', 'tournament']],
            ],
            [
                'name' => 'Technology Readers',
                'slug' => 'technology-readers',
                'description' => 'Users who frequently read technology news',
                'criteria' => ['keywords' => ['technology', 'tech', 'software', 'ai', 'startup']],
            ],
            [
                'name' => 'Business Readers',
                'slug' => 'business-readers',
                'description' => 'Users who frequently read business news',
                'criteria' => ['keywords' => ['business', 'economy', 'market', 'finance', 'stock']],
            ],
            [
                'name' => 'Entertainment Readers',
                'slug' => 'entertainment-readers',
                'description' => 'Users who frequently read entertainment news',
                'criteria' => ['keywords' => ['entertainment', 'movie', 'music', 'celebrity', 'bollywood']],
            ],
        ];

        foreach ($segments as $segment) {
            UserSegment::updateOrCreate(
                ['slug' => $segment['slug']],
                array_merge($segment, ['is_active' => true])
            );
        }
    }
}
