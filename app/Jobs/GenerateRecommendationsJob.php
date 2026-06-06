<?php

namespace App\Jobs;

use App\Enums\RecommendationFeedType;
use App\Models\User;
use App\Services\Recommendation\RecommendationEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GenerateRecommendationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId = null)
    {
        $this->onQueue('recommendations');
    }

    public function handle(RecommendationEngineService $engine): void
    {
        $users = $this->userId
            ? User::where('id', $this->userId)->get()
            : User::where('last_active_at', '>=', now()->subDays(7))->get();

        foreach ($users as $user) {
            foreach (RecommendationFeedType::cases() as $feedType) {
                $cacheKey = "recommendations:{$user->id}:{$feedType->value}";

                $feed = match ($feedType) {
                    RecommendationFeedType::ForYou => $engine->getForYouFeed($user, 20),
                    RecommendationFeedType::Following => $engine->getFollowingFeed($user, 20),
                    RecommendationFeedType::Trending => $engine->getTrendingFeed($user, 20),
                    RecommendationFeedType::Breaking => $engine->getBreakingFeed($user, 10),
                    RecommendationFeedType::Local => $engine->getLocalFeed($user, 20),
                };

                Cache::put($cacheKey, $feed, now()->addMinutes(15));
            }
        }
    }
}
