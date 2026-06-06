<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\BehaviorTrackingRequest;
use App\Http\Requests\Client\RecommendationFeedbackRequest;
use App\Http\Resources\RecommendationFeedResource;
use App\Http\Resources\UserInterestProfileResource;
use App\Services\Recommendation\RecommendationEngineService;
use App\Services\Recommendation\UserBehaviorTrackingService;
use App\Repositories\Contracts\RecommendationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RecommendationController extends Controller
{
    public function __construct(
        private readonly RecommendationEngineService $recommendationEngine,
        private readonly UserBehaviorTrackingService $behaviorTracking,
        private readonly RecommendationRepositoryInterface $recommendationRepository,
    ) {
    }

    public function feed(Request $request)
    {
        $user = $request->user();
        $sessionId = $request->get('session_id', Str::uuid()->toString());
        $perPage = (int) $request->get('per_page', config('recommendation.feed.default_per_page', 20));

        $cacheKey = "recommendations:{$user->id}:for_you";
        $feed = Cache::get($cacheKey);

        if (! $feed || $request->boolean('refresh')) {
            $feed = $this->recommendationEngine->getForYouFeed($user, $perPage, $sessionId);
            Cache::put($cacheKey, $feed, now()->addMinutes(15));
        }

        return new RecommendationFeedResource($feed);
    }

    public function trending(Request $request)
    {
        $feed = $this->recommendationEngine->getTrendingFeed(
            $request->user(),
            (int) $request->get('per_page', 20),
            $request->get('session_id'),
        );

        return new RecommendationFeedResource($feed);
    }

    public function local(Request $request)
    {
        $feed = $this->recommendationEngine->getLocalFeed(
            $request->user(),
            (int) $request->get('per_page', 20),
            $request->get('session_id'),
        );

        return new RecommendationFeedResource($feed);
    }

    public function following(Request $request)
    {
        $feed = $this->recommendationEngine->getFollowingFeed(
            $request->user(),
            (int) $request->get('per_page', 20),
            $request->get('session_id'),
        );

        return new RecommendationFeedResource($feed);
    }

    public function breaking(Request $request)
    {
        $feed = $this->recommendationEngine->getBreakingFeed(
            $request->user(),
            (int) $request->get('per_page', 10),
            $request->get('session_id'),
        );

        return new RecommendationFeedResource($feed);
    }

    public function feedback(RecommendationFeedbackRequest $request): JsonResponse
    {
        $this->recommendationEngine->recordFeedback($request->user(), $request->validated());

        return response()->json(['message' => 'Feedback recorded.']);
    }

    public function trackBehavior(BehaviorTrackingRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $type = $data['event_type'] instanceof \BackedEnum
            ? $data['event_type']->value
            : $data['event_type'];

        match ($type) {
            'article_open' => $this->behaviorTracking->trackArticleOpen(
                $user, $data['article_id'], $data['session_id'] ?? null, $data['feed_type'] ?? null
            ),
            'read_time' => $this->behaviorTracking->trackReadTime(
                $user, $data['article_id'], $data['read_time_seconds'] ?? 0,
                $data['scroll_depth_percent'] ?? null, $data['session_id'] ?? null
            ),
            'bookmark', 'unbookmark' => $this->behaviorTracking->trackBookmark(
                $user, $data['article_id'], $type === 'bookmark'
            ),
            'share' => $this->behaviorTracking->trackShare(
                $user, $data['article_id'], $data['share_platform'] ?? null
            ),
            'search' => $this->behaviorTracking->trackSearch(
                $user, $data['search_query'] ?? '', 0
            ),
            'category_open' => $this->behaviorTracking->trackCategoryOpen(
                $user, $data['category_id'], $data['session_id'] ?? null
            ),
            'source_open' => $this->behaviorTracking->trackSourceOpen(
                $user, $data['rss_source_id'], $data['session_id'] ?? null
            ),
            'session_start' => $this->behaviorTracking->trackSessionStart(
                $user, $data['session_id'] ?? Str::uuid()->toString()
            ),
            'session_end' => $this->behaviorTracking->trackSessionEnd(
                $user, $data['session_id'] ?? ''
            ),
            default => null,
        };

        return response()->json(['message' => 'Behavior tracked.']);
    }

    public function profile(Request $request): JsonResponse
    {
        $profile = $this->recommendationRepository->getOrCreateInterestProfile($request->user()->id);
        $profile->load([
            'categoryScores.category',
        ]);

        $user = $request->user();
        $user->load(['categoryScores.category', 'sourceScores.rssSource', 'topicScores']);

        return response()->json([
            'data' => new UserInterestProfileResource($profile),
            'category_scores' => $user->categoryScores->map(fn ($s) => [
                'category_id' => $s->category_id,
                'name' => $s->category?->name,
                'score' => (float) $s->score,
            ]),
            'source_scores' => $user->sourceScores->map(fn ($s) => [
                'source_id' => $s->rss_source_id,
                'name' => $s->rssSource?->name,
                'score' => (float) $s->score,
            ]),
            'topic_scores' => $user->topicScores->map(fn ($s) => [
                'topic' => $s->topic,
                'score' => (float) $s->score,
            ]),
        ]);
    }
}
