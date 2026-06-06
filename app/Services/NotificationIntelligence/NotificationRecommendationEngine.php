<?php

namespace App\Services\NotificationIntelligence;

use App\Enums\NotificationRecommendationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Models\Article;
use App\Models\NotificationRecommendation;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Collection;

class NotificationRecommendationEngine
{
    public function __construct(
        private readonly NotificationTargetingService $targetingService,
        private readonly NotificationFatigueService $fatigueService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function generateRecommendations(?int $userId = null): int
    {
        $count = 0;
        $articles = $this->getCandidateArticles();
        $users = $userId
            ? User::where('id', $userId)->whereNotNull('fcm_token')->get()
            : User::whereNotNull('fcm_token')->where('last_active_at', '>=', now()->subDays(14))->get();

        foreach ($users as $user) {
            $userRecs = $this->generateForUser($user, $articles);
            $count += $userRecs;
        }

        return $count;
    }

    public function generateForUser(User $user, ?Collection $articles = null): int
    {
        $articles ??= $this->getCandidateArticles();
        $maxRecs = config('notification_intelligence.recommendation.max_recommendations_per_user', 3);
        $ttlHours = config('notification_intelligence.recommendation.recommendation_ttl_hours', 6);
        $count = 0;

        $scored = $articles->map(function (Article $article) use ($user) {
            $result = $this->targetingService->scoreUserForArticle($user, $article);

            return [
                'article' => $article,
                'relevance' => $result['score'] / 100,
                'urgency' => min(1, (float) ($article->breaking_score ?? 0) / 30),
                'combined' => ($result['score'] / 100) * 0.7 + min(1, (float) ($article->breaking_score ?? 0) / 30) * 0.3,
                'breakdown' => $result['breakdown'],
                'eligible' => $result['eligible'],
            ];
        })
            ->filter(fn ($item) => $item['eligible'])
            ->sortByDesc('combined')
            ->take($maxRecs);

        foreach ($scored as $item) {
            $existing = NotificationRecommendation::where('user_id', $user->id)
                ->where('article_id', $item['article']->id)
                ->where('status', NotificationRecommendationStatus::Pending)
                ->exists();

            if ($existing) {
                continue;
            }

            NotificationRecommendation::create([
                'user_id' => $user->id,
                'article_id' => $item['article']->id,
                'relevance_score' => round($item['relevance'], 4),
                'urgency_score' => round($item['urgency'], 4),
                'combined_score' => round($item['combined'], 4),
                'optimal_send_at' => $this->fatigueService->getOptimalSendTime($user),
                'status' => NotificationRecommendationStatus::Pending,
                'reason' => $this->buildReason($item),
                'score_breakdown' => $item['breakdown'],
                'expires_at' => now()->addHours($ttlHours),
            ]);
            $count++;
        }

        return $count;
    }

    public function getPendingRecommendations(array $filters = [], int $perPage = 20)
    {
        $query = NotificationRecommendation::with(['user', 'article.category'])
            ->where('status', NotificationRecommendationStatus::Pending)
            ->where('expires_at', '>', now())
            ->orderByDesc('combined_score');

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    public function sendRecommendation(int $recommendationId, ?int $adminId = null): void
    {
        $rec = NotificationRecommendation::with(['user', 'article'])->findOrFail($recommendationId);

        if ($rec->status !== NotificationRecommendationStatus::Pending) {
            return;
        }

        $fatigue = $this->fatigueService->canReceiveNotification($rec->user);
        if (! $fatigue['allowed']) {
            $rec->update(['status' => NotificationRecommendationStatus::Skipped, 'reason' => $fatigue['reason']]);

            return;
        }

        $article = $rec->article;
        $notification = $this->notificationService->create([
            'title' => $article->title,
            'body' => $article->summary ?? \Illuminate\Support\Str::limit($article->title, 120),
            'image_url' => $article->image_url,
            'action_type' => 'article',
            'action_data' => ['article_id' => $article->id],
            'target_type' => NotificationTargetType::Users,
            'notification_type' => NotificationType::Recommendation,
            'article_id' => $article->id,
            'urgency_score' => $rec->urgency_score,
            'target_ids' => [$rec->user_id],
        ], $adminId ?? 1);

        $this->notificationService->dispatch($notification);

        $rec->update([
            'status' => NotificationRecommendationStatus::Sent,
            'notification_id' => $notification->id,
        ]);

        $this->fatigueService->recordSent($rec->user);
    }

    public function processDueRecommendations(): int
    {
        $due = NotificationRecommendation::where('status', NotificationRecommendationStatus::Pending)
            ->where('optimal_send_at', '<=', now())
            ->where('expires_at', '>', now())
            ->orderByDesc('combined_score')
            ->limit(100)
            ->get();

        foreach ($due as $rec) {
            $this->sendRecommendation($rec->id);
        }

        return $due->count();
    }

    private function getCandidateArticles(): Collection
    {
        return Article::query()
            ->published()
            ->where('published_at', '>=', now()->subDays(2))
            ->with(['category', 'tags', 'metrics'])
            ->orderByDesc('trending_score')
            ->limit(50)
            ->get();
    }

    private function buildReason(array $item): string
    {
        $breakdown = $item['breakdown'];
        $top = collect($breakdown)->sortDesc()->keys()->first();

        return "Matched via {$top} (score: ".round($item['combined'] * 100).')';
    }
}
