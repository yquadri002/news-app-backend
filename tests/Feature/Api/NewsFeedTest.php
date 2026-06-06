<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class NewsFeedTest extends TestCase
{
    public function test_public_news_feed_is_accessible(): void
    {
        $this->getJson('/api/v1/news/feed')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_public_news_endpoints_do_not_require_authentication(): void
    {
        $this->getJson('/api/v1/news/trending')->assertOk();
        $this->getJson('/api/v1/news/latest')->assertOk();
        $this->getJson('/api/v1/news/breaking')->assertOk();
    }
}
