<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Models\Admin;
use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Str;

class BreakingNewsService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly NotificationRepositoryInterface $notificationRepository,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function markBreaking(int $articleId, Admin $admin): Article
    {
        return $this->articleRepository->markBreaking($articleId, $admin->id);
    }

    public function pushToAll(int $articleId, Admin $admin): void
    {
        $article = $this->articleRepository->findOrFail($articleId);
        $this->articleRepository->markBreaking($articleId, $admin->id);

        $notification = $this->notificationRepository->create([
            'created_by' => $admin->id,
            'title' => 'Breaking: '.$article->title,
            'body' => $article->summary ?? Str($article->title)->limit(120),
            'image_url' => $article->image_url,
            'action_type' => 'article',
            'action_data' => ['article_id' => $article->id],
            'target_type' => NotificationTargetType::All,
            'status' => NotificationStatus::Scheduled,
            'scheduled_at' => now(),
        ]);

        $this->notificationService->dispatch($notification);
    }

    public function pushToCategories(int $articleId, array $categoryIds, Admin $admin): void
    {
        $article = $this->articleRepository->findOrFail($articleId);
        $this->articleRepository->markBreaking($articleId, $admin->id);

        $notification = $this->notificationRepository->create([
            'created_by' => $admin->id,
            'title' => 'Breaking: '.$article->title,
            'body' => $article->summary ?? Str($article->title)->limit(120),
            'image_url' => $article->image_url,
            'action_type' => 'article',
            'action_data' => ['article_id' => $article->id],
            'target_type' => NotificationTargetType::Categories,
            'status' => NotificationStatus::Scheduled,
            'scheduled_at' => now(),
        ]);

        foreach ($categoryIds as $categoryId) {
            $notification->targets()->create([
                'targetable_type' => \App\Models\Category::class,
                'targetable_id' => $categoryId,
            ]);
        }

        $this->notificationService->dispatch($notification);
    }

    public function pushToSegments(int $articleId, array $segmentIds, Admin $admin): void
    {
        $article = $this->articleRepository->findOrFail($articleId);
        $this->articleRepository->markBreaking($articleId, $admin->id);

        $notification = $this->notificationRepository->create([
            'created_by' => $admin->id,
            'title' => 'Breaking: '.$article->title,
            'body' => $article->summary ?? Str($article->title)->limit(120),
            'image_url' => $article->image_url,
            'action_type' => 'article',
            'action_data' => ['article_id' => $article->id],
            'target_type' => NotificationTargetType::Segments,
            'status' => NotificationStatus::Scheduled,
            'scheduled_at' => now(),
        ]);

        foreach ($segmentIds as $segmentId) {
            $notification->targets()->create([
                'targetable_type' => \App\Models\UserSegment::class,
                'targetable_id' => $segmentId,
            ]);
        }

        $this->notificationService->dispatch($notification);
    }
}
