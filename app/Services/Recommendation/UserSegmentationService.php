<?php

namespace App\Services\Recommendation;

use App\Models\User;
use App\Models\UserSegment;
use App\Models\UserSegmentMembership;
use App\Repositories\Contracts\RecommendationRepositoryInterface;

class UserSegmentationService
{
    public function __construct(
        private readonly RecommendationRepositoryInterface $recommendationRepository,
    ) {
    }

    public function assignSegments(User $user): array
    {
        $assigned = [];
        $topicScores = $this->recommendationRepository->getTopicScores($user->id);
        $categoryScores = $this->recommendationRepository->getCategoryScores($user->id);
        $segmentKeywords = config('recommendation.segments', []);

        foreach ($segmentKeywords as $slug => $keywords) {
            $score = $this->calculateSegmentScore($slug, $keywords, $topicScores, $categoryScores);

            if ($score < 5.0) {
                continue;
            }

            $segment = UserSegment::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => str_replace('-', ' ', ucwords($slug, '-')),
                    'criteria' => ['keywords' => $keywords],
                    'is_active' => true,
                ]
            );

            UserSegmentMembership::updateOrCreate(
                ['user_id' => $user->id, 'user_segment_id' => $segment->id],
                [
                    'confidence' => min(1.0, $score / 30),
                    'assigned_at' => now(),
                ]
            );

            $assigned[] = ['segment' => $slug, 'confidence' => min(1.0, $score / 30)];
        }

        if (! empty($assigned)) {
            $primary = collect($assigned)->sortByDesc('confidence')->first();
            $this->recommendationRepository->updateInterestProfile($user->id, [
                'primary_segment' => $primary['segment'],
            ]);
        }

        return $assigned;
    }

    public function assignAllActiveUsers(): int
    {
        $count = 0;
        $users = User::where('last_active_at', '>=', now()->subDays(30))->get();

        foreach ($users as $user) {
            $this->assignSegments($user);
            $count++;
        }

        return $count;
    }

    private function calculateSegmentScore(string $slug, array $keywords, $topicScores, $categoryScores): float
    {
        $score = 0;

        foreach ($topicScores as $topicScore) {
            foreach ($keywords as $keyword) {
                if (str_contains($topicScore->topic, $keyword)) {
                    $score += (float) $topicScore->score;
                }
            }
        }

        $categorySlug = str_replace('-readers', '', $slug);
        foreach ($categoryScores as $catScore) {
            if ($catScore->category?->slug === $categorySlug) {
                $score += (float) $catScore->score * 1.5;
            }
        }

        return $score;
    }
}
