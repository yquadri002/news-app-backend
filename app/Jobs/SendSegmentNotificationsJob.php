<?php

namespace App\Jobs;

use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Models\Article;
use App\Models\UserSegment;
use App\Services\NotificationIntelligence\NotificationFatigueService;
use App\Services\NotificationIntelligence\NotificationTargetingService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendSegmentNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $segmentId = null)
    {
        $this->onQueue('notifications');
    }

    public function handle(
        NotificationTargetingService $targetingService,
        NotificationFatigueService $fatigueService,
        NotificationService $notificationService,
    ): void {
        $segments = $this->segmentId
            ? UserSegment::where('id', $this->segmentId)->where('is_active', true)->get()
            : UserSegment::where('is_active', true)->get();

        $articles = Article::query()
            ->published()
            ->where('published_at', '>=', now()->subHours(12))
            ->orderByDesc('trending_score')
            ->limit(20)
            ->get();

        if ($articles->isEmpty()) {
            return;
        }

        foreach ($segments as $segment) {
            $users = $targetingService->getUsersForSegment($segment->id);

            if ($users->isEmpty()) {
                continue;
            }

            $article = $this->selectArticleForSegment($segment, $articles);
            if (! $article) {
                continue;
            }

            $eligibleIds = $users->filter(
                fn ($user) => $fatigueService->canReceiveNotification($user)['allowed']
            )->pluck('id')->toArray();

            if (empty($eligibleIds)) {
                continue;
            }

            $notification = $notificationService->create([
                'title' => Str::limit($article->title, 80),
                'body' => $article->summary ?? Str::limit($article->title, 120),
                'image_url' => $article->image_url,
                'action_type' => 'article',
                'action_data' => ['article_id' => $article->id],
                'target_type' => NotificationTargetType::Users,
                'notification_type' => NotificationType::Automated,
                'article_id' => $article->id,
                'target_ids' => $eligibleIds,
            ], 1);

            $notificationService->dispatch($notification);

            foreach ($users as $user) {
                if (in_array($user->id, $eligibleIds)) {
                    $fatigueService->recordSent($user);
                }
            }
        }
    }

    private function selectArticleForSegment(UserSegment $segment, $articles): ?Article
    {
        $keywords = $segment->criteria['keywords'] ?? [];
        if (empty($keywords)) {
            return $articles->first();
        }

        foreach ($articles as $article) {
            $text = strtolower($article->title.' '.($article->summary ?? ''));
            foreach ($keywords as $keyword) {
                if (str_contains($text, strtolower($keyword))) {
                    return $article;
                }
            }
        }

        return $articles->first();
    }
}
