<?php

namespace App\Services\NotificationIntelligence;

use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Models\Article;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BreakingNewsAutomationService
{
    public function __construct(
        private readonly NotificationTargetingService $targetingService,
        private readonly NotificationFatigueService $fatigueService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function processBreakingArticles(): int
    {
        if (! config('notification_intelligence.breaking.auto_push_enabled', true)) {
            return 0;
        }

        $threshold = config('notification_intelligence.breaking.urgency_threshold', 15.0);
        $cooldownMinutes = config('notification_intelligence.breaking.cooldown_minutes', 30);

        $articles = Article::query()
            ->published()
            ->where('is_breaking', true)
            ->where('breaking_score', '>=', $threshold)
            ->where('published_at', '>=', now()->subHours(6))
            ->with(['category', 'metrics'])
            ->orderByDesc('breaking_score')
            ->get();

        $sent = 0;

        foreach ($articles as $article) {
            $cacheKey = "breaking_push:{$article->id}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            if ($this->wasAlreadyPushed($article)) {
                continue;
            }

            $targetUsers = $this->targetingService->getTargetUsersForArticle($article, 1000);

            if ($targetUsers->isEmpty()) {
                continue;
            }

            $this->pushBreakingToUsers($article, $targetUsers);
            Cache::put($cacheKey, true, now()->addMinutes($cooldownMinutes));
            $sent++;
        }

        return $sent;
    }

    public function scoreUrgency(Article $article): float
    {
        $breakingScore = (float) ($article->breaking_score ?? 0);
        $confirmation = (float) ($article->metrics?->source_confirmation_count ?? 1);
        $recency = max(0, 30 - (now()->diffInMinutes($article->published_at ?? now()) / 10));

        return round($breakingScore * 0.5 + $confirmation * 5 + $recency * 0.5, 4);
    }

    private function pushBreakingToUsers(Article $article, $users): Notification
    {
        $eligibleUserIds = $users->filter(function ($user) {
            return $this->fatigueService->canReceiveNotification($user)['allowed'];
        })->pluck('id')->toArray();

        $notification = $this->notificationService->create([
            'title' => 'Breaking: '.Str::limit($article->title, 80),
            'body' => $article->summary ?? Str::limit($article->title, 120),
            'image_url' => $article->image_url,
            'action_type' => 'article',
            'action_data' => ['article_id' => $article->id],
            'target_type' => NotificationTargetType::Users,
            'notification_type' => NotificationType::Breaking,
            'article_id' => $article->id,
            'urgency_score' => $this->scoreUrgency($article),
            'target_ids' => $eligibleUserIds,
        ], 1);

        $this->notificationService->dispatch($notification);

        foreach ($users as $user) {
            if (in_array($user->id, $eligibleUserIds)) {
                $this->fatigueService->recordSent($user);
            }
        }

        return $notification;
    }

    private function wasAlreadyPushed(Article $article): bool
    {
        return Notification::where('article_id', $article->id)
            ->where('notification_type', NotificationType::Breaking)
            ->where('created_at', '>=', now()->subHours(6))
            ->exists();
    }
}
