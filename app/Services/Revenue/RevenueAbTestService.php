<?php

namespace App\Services\Revenue;

use App\Enums\AbTestStatus;
use App\Enums\RevenueAbTestType;
use App\Models\AbTestResult;
use App\Repositories\Contracts\AbTestResultRepositoryInterface;
use Illuminate\Support\Collection;

class RevenueAbTestService
{
    public function __construct(
        private readonly AbTestResultRepositoryInterface $abTestRepository,
    ) {
    }

    public function createTest(string $name, RevenueAbTestType $type, array $variants): AbTestResult
    {
        return $this->abTestRepository->create([
            'name' => $name,
            'test_type' => $type,
            'variants' => $variants,
            'status' => AbTestStatus::Active,
            'started_at' => now(),
        ]);
    }

    public function assignVariant(AbTestResult $test): array
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

    public function recordImpression(AbTestResult $test): void
    {
        $test->increment('impressions');
    }

    public function recordConversion(AbTestResult $test, float $revenue = 0): void
    {
        $test->increment('conversions');
        if ($revenue > 0) {
            $test->increment('revenue', $revenue);
        }
    }

    public function determineWinner(AbTestResult $test): ?string
    {
        $minSample = config('revenue.ab_testing.min_sample_size', 500);
        if ($test->impressions < $minSample) {
            return null;
        }

        $variants = collect($test->variants);
        $best = $variants->sortByDesc(fn ($v) => ($v['conversions'] ?? 0) / max(1, $v['impressions'] ?? 1))->first();
        $winner = $best['key'] ?? 'A';

        $test->update([
            'winning_variant' => $winner,
            'status' => AbTestStatus::Completed,
            'ended_at' => now(),
        ]);

        return $winner;
    }

    public function getActiveTests(): Collection
    {
        return $this->abTestRepository->getActiveTests();
    }

    public function listTests(array $filters = [], int $perPage = 15)
    {
        return $this->abTestRepository->paginate($perPage, $filters);
    }
}
