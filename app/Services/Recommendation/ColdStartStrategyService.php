<?php

namespace App\Services\Recommendation;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;

class ColdStartStrategyService
{
    public function getCategoryScores(User $user): Collection
    {
        $scores = collect();
        $prefs = $user->preferences;

        if ($prefs?->category_ids) {
            foreach ($prefs->category_ids as $categoryId) {
                $scores->put($categoryId, (object) [
                    'category_id' => $categoryId,
                    'score' => 15.0,
                ]);
            }
        }

        if ($scores->isEmpty()) {
            $popular = Category::where('is_enabled', true)
                ->orderBy('sort_order')
                ->limit(config('recommendation.cold_start.popular_category_limit', 5))
                ->pluck('id');

            foreach ($popular as $categoryId) {
                $scores->put($categoryId, (object) [
                    'category_id' => $categoryId,
                    'score' => 10.0,
                ]);
            }
        }

        if ($user->language) {
            $langCategory = $this->getLanguageCategory($user->language);
            if ($langCategory) {
                $scores->put($langCategory, (object) [
                    'category_id' => $langCategory,
                    'score' => ($scores[$langCategory]->score ?? 0) + 5.0,
                ]);
            }
        }

        return $scores;
    }

    public function getTopicScores(User $user): Collection
    {
        $scores = collect();
        $prefs = $user->preferences;

        foreach ($prefs?->interests ?? [] as $interest) {
            $scores->put(strtolower($interest), (object) [
                'topic' => strtolower($interest),
                'score' => 12.0,
            ]);
        }

        if ($scores->isEmpty()) {
            foreach (['world', 'technology', 'business'] as $topic) {
                $scores->put($topic, (object) ['topic' => $topic, 'score' => 8.0]);
            }
        }

        return $scores;
    }

    public function getOnboardingBoost(User $user): array
    {
        return [
            'language' => $user->language ?? $user->preferences?->language ?? 'en',
            'location' => $user->location ?? $user->preferences?->location,
            'interests' => $user->preferences?->interests ?? [],
            'categories' => $user->preferences?->category_ids ?? [],
        ];
    }

    private function getLanguageCategory(string $language): ?int
    {
        $map = [
            'hi' => 'india',
            'en' => 'world',
        ];

        $slug = $map[$language] ?? null;
        if (! $slug) {
            return null;
        }

        return Category::where('slug', $slug)->value('id');
    }
}
