<?php

namespace App\Jobs;

use App\Services\Recommendation\InterestScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateInterestProfilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId = null)
    {
        $this->onQueue('recommendations');
    }

    public function handle(InterestScoringService $interestScoring): void
    {
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            if ($user) {
                $interestScoring->calculateForUser($user);
            }

            return;
        }

        $interestScoring->calculateForAllActiveUsers();
    }
}
