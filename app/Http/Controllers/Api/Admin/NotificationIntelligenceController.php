<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\DigestType;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendIntelligentNotificationRequest;
use App\Http\Requests\Admin\TestNotificationRequest;
use App\Http\Resources\NotificationRecommendationResource;
use App\Http\Resources\NotificationResource;
use App\Jobs\SendSegmentNotificationsJob;
use App\Models\Article;
use App\Models\User;
use App\Services\FcmService;
use App\Services\NotificationIntelligence\BreakingNewsAutomationService;
use App\Services\NotificationIntelligence\DigestService;
use App\Services\NotificationIntelligence\NotificationIntelligenceAnalyticsService;
use App\Services\NotificationIntelligence\NotificationRecommendationEngine;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationIntelligenceController extends Controller
{
    public function __construct(
        private readonly NotificationRecommendationEngine $recommendationEngine,
        private readonly NotificationIntelligenceAnalyticsService $analyticsService,
        private readonly NotificationService $notificationService,
        private readonly BreakingNewsAutomationService $breakingAutomation,
        private readonly DigestService $digestService,
        private readonly FcmService $fcmService,
    ) {
    }

    public function recommendations(Request $request): JsonResponse
    {
        $recommendations = $this->recommendationEngine->getPendingRecommendations(
            $request->only(['user_id']),
            (int) $request->get('per_page', 20),
        );

        return response()->json([
            'data' => NotificationRecommendationResource::collection($recommendations),
            'meta' => [
                'current_page' => $recommendations->currentPage(),
                'last_page' => $recommendations->lastPage(),
                'per_page' => $recommendations->perPage(),
                'total' => $recommendations->total(),
            ],
        ]);
    }

    public function send(SendIntelligentNotificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $adminId = $request->user()->id;

        $result = match ($data['type']) {
            NotificationType::Recommendation->value => $this->sendRecommendation($data, $adminId),
            NotificationType::Breaking->value => $this->sendBreaking($data, $adminId),
            NotificationType::Digest->value => $this->sendDigest($data),
            NotificationType::Automated->value => $this->sendSegment($data),
            default => $this->sendManual($data, $adminId),
        };

        return response()->json([
            'message' => 'Notification dispatch initiated.',
            'data' => $result,
        ]);
    }

    public function test(TestNotificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $adminId = $request->user()->id;

        $user = null;
        if (! empty($data['user_id'])) {
            $user = User::findOrFail($data['user_id']);
        }

        $notification = $this->notificationService->create([
            'title' => $data['title'],
            'body' => $data['body'],
            'action_type' => ! empty($data['article_id']) ? 'article' : 'test',
            'action_data' => ! empty($data['article_id']) ? ['article_id' => $data['article_id']] : [],
            'target_type' => NotificationTargetType::Users,
            'notification_type' => NotificationType::Manual,
            'article_id' => $data['article_id'] ?? null,
            'target_ids' => $user ? [$user->id] : [],
        ], $adminId);

        if ($user) {
            $this->notificationService->dispatch($notification);
        } elseif (! empty($data['fcm_token'])) {
            $this->fcmService->sendToToken($data['fcm_token'], $notification);
        }

        return response()->json([
            'message' => 'Test notification sent.',
            'data' => new NotificationResource($notification->fresh()),
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getAnalytics([
                'from' => $request->get('from'),
                'to' => $request->get('to'),
            ]),
        ]);
    }

    public function calculateSnapshot(): JsonResponse
    {
        $this->analyticsService->calculateDailySnapshot();

        return response()->json(['message' => 'Notification analytics snapshot calculated.']);
    }

    private function sendRecommendation(array $data, int $adminId): array
    {
        if (empty($data['recommendation_id'])) {
            abort(422, 'recommendation_id is required for recommendation notifications.');
        }

        $this->recommendationEngine->sendRecommendation($data['recommendation_id'], $adminId);

        return ['type' => 'recommendation', 'recommendation_id' => $data['recommendation_id']];
    }

    private function sendBreaking(array $data, int $adminId): array
    {
        if (! empty($data['article_id'])) {
            $article = Article::findOrFail($data['article_id']);
            $users = app(\App\Services\NotificationIntelligence\NotificationTargetingService::class)
                ->getTargetUsersForArticle($article, 1000);

            $notification = $this->notificationService->create([
                'title' => 'Breaking: '.Str::limit($article->title, 80),
                'body' => $article->summary ?? Str::limit($article->title, 120),
                'image_url' => $article->image_url,
                'action_type' => 'article',
                'action_data' => ['article_id' => $article->id],
                'target_type' => NotificationTargetType::Users,
                'notification_type' => NotificationType::Breaking,
                'article_id' => $article->id,
                'urgency_score' => $this->breakingAutomation->scoreUrgency($article),
                'target_ids' => $users->pluck('id')->toArray(),
            ], $adminId);

            $this->notificationService->dispatch($notification);

            return ['type' => 'breaking', 'notification_id' => $notification->id];
        }

        $sent = $this->breakingAutomation->processBreakingArticles();

        return ['type' => 'breaking', 'articles_processed' => $sent];
    }

    private function sendDigest(array $data): array
    {
        $digestType = ! empty($data['digest_type'])
            ? DigestType::from($data['digest_type'])
            : DigestType::Morning;

        $digest = $this->digestService->generateDigest($digestType);
        $sent = $this->digestService->sendDigest($digest);

        return ['type' => 'digest', 'digest_id' => $digest->id, 'sent_count' => $sent];
    }

    private function sendSegment(array $data): array
    {
        SendSegmentNotificationsJob::dispatch($data['segment_id'] ?? null);

        return ['type' => 'segment', 'segment_id' => $data['segment_id'] ?? null, 'queued' => true];
    }

    private function sendManual(array $data, int $adminId): array
    {
        if (empty($data['user_ids'])) {
            abort(422, 'user_ids is required for manual notifications.');
        }

        $notification = $this->notificationService->create([
            'title' => 'News Update',
            'body' => 'You have a new notification.',
            'action_type' => 'article',
            'action_data' => ! empty($data['article_id']) ? ['article_id' => $data['article_id']] : [],
            'target_type' => NotificationTargetType::Users,
            'notification_type' => NotificationType::Manual,
            'article_id' => $data['article_id'] ?? null,
            'target_ids' => $data['user_ids'],
        ], $adminId);

        $this->notificationService->dispatch($notification);

        return ['type' => 'manual', 'notification_id' => $notification->id];
    }
}
