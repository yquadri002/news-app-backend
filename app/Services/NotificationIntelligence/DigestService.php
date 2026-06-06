<?php

namespace App\Services\NotificationIntelligence;

use App\Enums\DigestType;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Models\Article;
use App\Models\NotificationDigest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Str;

class DigestService
{
    public function __construct(
        private readonly NotificationFatigueService $fatigueService,
        private readonly NotificationTargetingService $targetingService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function generateDigest(DigestType $type, ?string $date = null): NotificationDigest
    {
        $digestDate = $date ?? now()->toDateString();
        $articles = $this->selectDigestArticles($type);
        $sendHour = $type->defaultSendHour();

        $digest = NotificationDigest::updateOrCreate(
            ['digest_type' => $type, 'digest_date' => $digestDate],
            [
                'status' => 'pending',
                'article_ids' => $articles->pluck('id')->toArray(),
                'scheduled_at' => now()->parse($digestDate)->setTime($sendHour, 0),
            ]
        );

        return $digest;
    }

    public function sendDigest(NotificationDigest $digest): int
    {
        $articles = Article::whereIn('id', $digest->article_ids ?? [])->get();
        if ($articles->isEmpty()) {
            return 0;
        }

        $users = User::whereNotNull('fcm_token')
            ->whereHas('preferences', fn ($q) => $q->where('notifications_enabled', true))
            ->get();

        $sent = 0;
        $topArticle = $articles->first();
        $title = $digest->digest_type->label().': '.$articles->count().' stories for you';
        $body = $articles->take(3)->pluck('title')->map(fn ($t) => '• '.Str::limit($t, 60))->join("\n");

        foreach ($users as $user) {
            if (! $this->fatigueService->canReceiveNotification($user)['allowed']) {
                continue;
            }

            $notification = $this->notificationService->create([
                'title' => $title,
                'body' => $body,
                'image_url' => $topArticle?->image_url,
                'action_type' => 'digest',
                'action_data' => [
                    'digest_type' => $digest->digest_type->value,
                    'article_ids' => $digest->article_ids,
                ],
                'target_type' => NotificationTargetType::Users,
                'notification_type' => NotificationType::Digest,
                'digest_type' => $digest->digest_type,
                'article_id' => $topArticle?->id,
                'target_ids' => [$user->id],
            ], 1);

            $this->notificationService->dispatch($notification);
            $this->fatigueService->recordSent($user);
            $sent++;
        }

        $digest->update([
            'status' => 'sent',
            'sent_at' => now(),
            'target_user_count' => $users->count(),
            'sent_count' => $sent,
        ]);

        return $sent;
    }

    public function generateAllDigests(?string $date = null): array
    {
        $results = [];
        foreach (DigestType::cases() as $type) {
            $digest = $this->generateDigest($type, $date);
            $results[$type->value] = $digest;
        }

        return $results;
    }

    private function selectDigestArticles(DigestType $type): \Illuminate\Support\Collection
    {
        $limit = config('notification_intelligence.digest.articles_per_digest', 5);
        $hoursBack = match ($type) {
            DigestType::Morning => 12,
            DigestType::Afternoon => 6,
            DigestType::Evening => 8,
        };

        return Article::query()
            ->published()
            ->where('published_at', '>=', now()->subHours($hoursBack))
            ->orderByDesc('trending_score')
            ->limit($limit)
            ->get();
    }
}
