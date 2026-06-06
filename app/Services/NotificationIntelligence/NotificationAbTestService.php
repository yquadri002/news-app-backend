<?php

namespace App\Services\NotificationIntelligence;

use App\Enums\AbTestType;
use App\Models\Notification;
use App\Models\NotificationAbTest;
use Illuminate\Support\Collection;

class NotificationAbTestService
{
    public function createTest(string $name, AbTestType $type, array $variants): NotificationAbTest
    {
        return NotificationAbTest::create([
            'name' => $name,
            'test_type' => $type,
            'variants' => $variants,
            'is_active' => true,
            'started_at' => now(),
        ]);
    }

    public function assignVariant(NotificationAbTest $test): array
    {
        $roll = random_int(1, 100);
        $cumulative = 0;

        foreach ($test->variants as $variant) {
            $cumulative += $variant['traffic_percentage'] ?? 50;
            if ($roll <= $cumulative) {
                return $variant;
            }
        }

        return $test->variants[0] ?? [];
    }

    public function applyToNotification(Notification $notification, NotificationAbTest $test): Notification
    {
        $variant = $this->assignVariant($test);

        $updates = ['ab_test_id' => $test->id, 'ab_test_variant' => $variant['key'] ?? 'A'];

        if ($test->test_type === AbTestType::Title && ! empty($variant['title'])) {
            $updates['title'] = $variant['title'];
            if (! empty($variant['body'])) {
                $updates['body'] = $variant['body'];
            }
        }

        $notification->update($updates);
        $test->increment('impressions');

        return $notification->fresh();
    }

    public function recordClick(NotificationAbTest $test, string $variantKey): void
    {
        $test->increment('clicks');
    }

    public function recordConversion(NotificationAbTest $test): void
    {
        $test->increment('conversions');
    }

    public function determineWinner(NotificationAbTest $test): ?string
    {
        if ($test->impressions < config('notification_intelligence.ab_testing.min_sample_size', 100)) {
            return null;
        }

        $variants = collect($test->variants);
        $bestVariant = $variants->sortByDesc(fn ($v) => ($v['clicks'] ?? 0) / max(1, $v['impressions'] ?? 1))->first();

        $winner = $bestVariant['key'] ?? 'A';
        $test->update(['winning_variant' => $winner, 'is_active' => false, 'ended_at' => now()]);

        return $winner;
    }

    public function getActiveTests(): Collection
    {
        return NotificationAbTest::where('is_active', true)->get();
    }
}
