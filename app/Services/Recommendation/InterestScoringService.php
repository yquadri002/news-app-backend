<?php

namespace App\Services\Recommendation;

use App\Enums\BehaviorEventType;
use App\Models\Article;
use App\Models\User;
use App\Repositories\Contracts\RecommendationRepositoryInterface;
use App\Repositories\Contracts\UserBehaviorRepositoryInterface;
use Illuminate\Support\Collection;

class InterestScoringService
{
    private const EVENT_WEIGHTS = [
        'article_open' => 3.0,
        'read_time' => 5.0,
        'scroll_depth' => 2.0,
        'bookmark' => 8.0,
        'share' => 10.0,
        'search' => 2.0,
        'category_open' => 4.0,
        'source_open' => 4.0,
    ];

    public function __construct(
        private readonly RecommendationRepositoryInterface $recommendationRepository,
        private readonly UserBehaviorRepositoryInterface $behaviorRepository,
    ) {
    }

    public function calculateForUser(User $user): void
    {
        $events = $this->behaviorRepository->getRecentForUser($user->id, config('recommendation.interest_decay_days', 30));
        $prefs = $user->preferences;

        $this->applyExplicitPreferences($user, $prefs);
        $this->applyImplicitSignals($user, $events);

        $totalEvents = $events->count();
        $profileStrength = min(1.0, $totalEvents / config('recommendation.cold_start.min_events_for_warm', 10));
        $topTopics = $this->recommendationRepository->getTopicScores($user->id)->take(10)->pluck('topic')->toArray();

        $this->recommendationRepository->updateInterestProfile($user->id, [
            'is_cold_start' => $totalEvents < config('recommendation.cold_start.min_events_for_warm', 10),
            'profile_strength' => round($profileStrength, 4),
            'total_events' => $totalEvents,
            'top_topics' => $topTopics,
            'last_calculated_at' => now(),
        ]);
    }

    public function calculateForAllActiveUsers(): int
    {
        $users = User::where('last_active_at', '>=', now()->subDays(30))->get();
        foreach ($users as $user) {
            $this->calculateForUser($user);
        }

        return $users->count();
    }

    private function applyExplicitPreferences(User $user, $prefs): void
    {
        if (! $prefs) {
            return;
        }

        foreach ($prefs->category_ids ?? [] as $categoryId) {
            $this->recommendationRepository->upsertCategoryScore($user->id, (int) $categoryId, [
                'explicit_score' => 10.0,
                'score' => 10.0,
                'last_interaction_at' => now(),
            ]);
        }

        foreach ($prefs->source_ids ?? [] as $sourceId) {
            $this->recommendationRepository->upsertSourceScore($user->id, (int) $sourceId, [
                'explicit_score' => 10.0,
                'score' => 10.0,
                'last_interaction_at' => now(),
            ]);
        }

        foreach ($prefs->interests ?? [] as $interest) {
            $this->recommendationRepository->upsertTopicScore($user->id, $interest, [
                'score' => 8.0,
                'interaction_count' => 1,
                'last_interaction_at' => now(),
            ]);
        }
    }

    private function applyImplicitSignals(User $user, Collection $events): void
    {
        $categorySignals = [];
        $sourceSignals = [];
        $topicSignals = [];

        foreach ($events as $event) {
            $type = $event->event_type->value ?? $event->event_type;
            $weight = self::EVENT_WEIGHTS[$type] ?? 1.0;
            $decay = $this->timeDecay($event->occurred_at);

            if ($event->category_id) {
                $categorySignals[$event->category_id] = ($categorySignals[$event->category_id] ?? 0) + ($weight * $decay);
            }

            if ($event->rss_source_id) {
                $sourceSignals[$event->rss_source_id] = ($sourceSignals[$event->rss_source_id] ?? 0) + ($weight * $decay);
            }

            if ($event->article_id) {
                $article = Article::with('tags')->find($event->article_id);
                if ($article) {
                    if ($article->category_id) {
                        $categorySignals[$article->category_id] = ($categorySignals[$article->category_id] ?? 0) + ($weight * $decay);
                    }
                    if ($article->rss_source_id) {
                        $sourceSignals[$article->rss_source_id] = ($sourceSignals[$article->rss_source_id] ?? 0) + ($weight * $decay);
                    }
                    foreach ($article->tags ?? [] as $tag) {
                        $topicSignals[$tag->tag] = ($topicSignals[$tag->tag] ?? 0) + ($weight * $decay);
                    }
                }
            }

            if ($event->search_query) {
                $words = array_filter(explode(' ', strtolower($event->search_query)), fn ($w) => strlen($w) > 3);
                foreach ($words as $word) {
                    $topicSignals[$word] = ($topicSignals[$word] ?? 0) + ($weight * $decay);
                }
            }

            if ($type === BehaviorEventType::ReadTime->value && $event->read_time_seconds > 30) {
                $bonus = min(5, $event->read_time_seconds / 60);
                if ($event->article_id) {
                    $article = Article::find($event->article_id);
                    if ($article?->category_id) {
                        $categorySignals[$article->category_id] = ($categorySignals[$article->category_id] ?? 0) + $bonus;
                    }
                }
            }
        }

        $this->persistSignals($user->id, $categorySignals, $sourceSignals, $topicSignals);
    }

    private function persistSignals(int $userId, array $categories, array $sources, array $topics): void
    {
        foreach ($categories as $categoryId => $score) {
            $existing = $this->recommendationRepository->getCategoryScores($userId)
                ->firstWhere('category_id', $categoryId);
            $explicit = $existing?->explicit_score ?? 0;

            $this->recommendationRepository->upsertCategoryScore($userId, (int) $categoryId, [
                'implicit_score' => round($score, 4),
                'score' => round($explicit + $score, 4),
                'interaction_count' => ($existing?->interaction_count ?? 0) + 1,
                'last_interaction_at' => now(),
            ]);
        }

        foreach ($sources as $sourceId => $score) {
            $existing = $this->recommendationRepository->getSourceScores($userId)
                ->firstWhere('rss_source_id', $sourceId);
            $explicit = $existing?->explicit_score ?? 0;

            $this->recommendationRepository->upsertSourceScore($userId, (int) $sourceId, [
                'implicit_score' => round($score, 4),
                'score' => round($explicit + $score, 4),
                'interaction_count' => ($existing?->interaction_count ?? 0) + 1,
                'last_interaction_at' => now(),
            ]);
        }

        foreach ($topics as $topic => $score) {
            $this->recommendationRepository->upsertTopicScore($userId, $topic, [
                'score' => round($score, 4),
                'interaction_count' => 1,
                'last_interaction_at' => now(),
            ]);
        }
    }

    private function timeDecay(\DateTimeInterface $occurredAt): float
    {
        $daysAgo = now()->diffInDays($occurredAt);
        $halfLife = config('recommendation.interest_decay_days', 30) / 2;

        return pow(0.5, $daysAgo / max(1, $halfLife));
    }
}
