<?php

namespace App\Services\NotificationIntelligence;

use App\Models\Article;
use App\Models\User;
use App\Repositories\Contracts\UserBehaviorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class NotificationTargetingService
{
    public function __construct(
        private readonly NotificationFatigueService $fatigueService,
        private readonly UserBehaviorRepositoryInterface $behaviorRepository,
    ) {
    }

    public function scoreUserForArticle(User $user, Article $article): array
    {
        $breakdown = [
            'interest' => $this->scoreInterestMatch($user, $article),
            'segment' => $this->scoreSegmentMatch($user, $article),
            'read_history' => $this->scoreReadHistory($user, $article),
            'location' => $this->scoreLocationMatch($user, $article),
            'language' => $this->scoreLanguageMatch($user, $article),
            'freshness' => $this->scoreFreshness($article),
        ];

        $weights = ['interest' => 0.35, 'segment' => 0.20, 'read_history' => 0.10, 'location' => 0.10, 'language' => 0.10, 'freshness' => 0.15];

        $total = 0;
        foreach ($breakdown as $factor => $score) {
            $total += $score * ($weights[$factor] ?? 0);
        }

        $fatigue = $this->fatigueService->canReceiveNotification($user);
        if (! $fatigue['allowed']) {
            $total *= 0.1;
        } elseif (isset($fatigue['sensitivity_penalty'])) {
            $total *= (1 - $fatigue['sensitivity_penalty'] * 0.5);
        }

        return [
            'score' => round($total, 4),
            'breakdown' => $breakdown,
            'eligible' => $fatigue['allowed'] && $total >= config('notification_intelligence.recommendation.min_relevance_score', 0.3),
            'fatigue' => $fatigue,
        ];
    }

    public function getTargetUsersForArticle(Article $article, int $limit = 500): Collection
    {
        $users = User::query()
            ->whereNotNull('fcm_token')
            ->whereHas('preferences', fn ($q) => $q->where('notifications_enabled', true))
            ->with(['preferences', 'interestProfile', 'categoryScores', 'segmentMemberships', 'notificationState'])
            ->get();

        $scored = $users->map(function (User $user) use ($article) {
            $result = $this->scoreUserForArticle($user, $article);

            return ['user' => $user, 'score' => $result['score'], 'breakdown' => $result['breakdown'], 'eligible' => $result['eligible']];
        })
            ->filter(fn ($item) => $item['eligible'])
            ->sortByDesc('score')
            ->take($limit);

        return $scored->pluck('user');
    }

    public function getUsersForSegment(int $segmentId): Collection
    {
        return User::query()
            ->whereNotNull('fcm_token')
            ->whereHas('segmentMemberships', fn ($q) => $q->where('user_segment_id', $segmentId))
            ->whereHas('preferences', fn ($q) => $q->where('notifications_enabled', true))
            ->with(['notificationState', 'preferences'])
            ->get()
            ->filter(fn (User $user) => $this->fatigueService->canReceiveNotification($user)['allowed']);
    }

    private function scoreInterestMatch(User $user, Article $article): float
    {
        $score = 0;
        $catId = $article->category_id ?? $article->auto_category_id;

        if ($catId) {
            $catScore = $user->categoryScores->firstWhere('category_id', $catId);
            $score += $catScore ? min(100, (float) $catScore->score * 3) : 0;
        }

        if ($article->rss_source_id) {
            $srcScore = $user->sourceScores->firstWhere('rss_source_id', $article->rss_source_id);
            $score += $srcScore ? min(50, (float) $srcScore->score * 2) : 0;
        }

        $prefs = $user->preferences;
        if ($prefs?->category_ids && $catId && in_array($catId, $prefs->category_ids)) {
            $score += 30;
        }

        return min(100, $score);
    }

    private function scoreSegmentMatch(User $user, Article $article): float
    {
        if ($user->segmentMemberships->isEmpty()) {
            return 30;
        }

        $articleText = strtolower($article->title.' '.($article->summary ?? ''));
        $maxConfidence = 0;

        foreach ($user->segmentMemberships as $membership) {
            $segment = $membership->segment;
            if (! $segment) {
                continue;
            }
            $keywords = $segment->criteria['keywords'] ?? [];
            foreach ($keywords as $keyword) {
                if (str_contains($articleText, $keyword)) {
                    $maxConfidence = max($maxConfidence, (float) $membership->confidence);
                }
            }
        }

        return $maxConfidence * 100;
    }

    private function scoreReadHistory(User $user, Article $article): float
    {
        $readIds = $this->behaviorRepository->getReadArticleIds($user->id, 7);
        if (in_array($article->id, $readIds)) {
            return 0;
        }

        $catId = $article->category_id ?? $article->auto_category_id;
        if (! $catId) {
            return 50;
        }

        $categoryReads = DB::table('user_behavior_events')
            ->join('articles', 'user_behavior_events.article_id', '=', 'articles.id')
            ->where('user_behavior_events.user_id', $user->id)
            ->where('articles.category_id', $catId)
            ->where('user_behavior_events.occurred_at', '>=', now()->subDays(14))
            ->count();

        return min(100, $categoryReads * 10);
    }

    private function scoreLocationMatch(User $user, Article $article): float
    {
        $location = strtolower($user->location ?? $user->preferences?->location ?? '');
        if (empty($location)) {
            return 50;
        }

        $text = strtolower($article->title.' '.($article->summary ?? ''));

        return str_contains($text, $location) ? 100 : 20;
    }

    private function scoreLanguageMatch(User $user, Article $article): float
    {
        $userLang = $user->language ?? $user->preferences?->language ?? 'en';

        return $userLang === 'en' ? 80 : 60;
    }

    private function scoreFreshness(Article $article): float
    {
        $hoursAgo = now()->diffInMinutes($article->published_at ?? $article->created_at) / 60;

        return max(0, 100 * pow(0.5, $hoursAgo / 12));
    }
}
