<?php

namespace App\Services;

use App\Models\AdAbTest;
use App\Models\AdPlacement;
use App\Repositories\Contracts\AdPlacementRepositoryInterface;

class AdPlacementService
{
    public function __construct(
        private readonly AdPlacementRepositoryInterface $adPlacementRepository,
    ) {
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->adPlacementRepository->paginate($perPage, $filters);
    }

    public function create(array $data): AdPlacement
    {
        return $this->adPlacementRepository->create($data);
    }

    public function update(int $id, array $data): AdPlacement
    {
        return $this->adPlacementRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->adPlacementRepository->delete($id);
    }

    public function getRemoteConfig(): array
    {
        return $this->adPlacementRepository->getEnabledWithConfig()
            ->map(fn (AdPlacement $placement) => [
                'placement_key' => $placement->placement_key,
                'format' => $placement->format,
                'frequency_cap' => $placement->frequency_cap,
                'frequency_period_minutes' => $placement->frequency_period_minutes,
                'remote_config' => $placement->remote_config,
                'ab_test_variant' => $this->resolveAbVariant($placement),
            ])
            ->values()
            ->toArray();
    }

    public function createAbTest(int $placementId, array $data): AdAbTest
    {
        $placement = $this->adPlacementRepository->findOrFail($placementId);

        return $placement->abTests()->create($data);
    }

    public function updateAbTest(int $testId, array $data): AdAbTest
    {
        $test = AdAbTest::findOrFail($testId);
        $test->update($data);

        return $test->fresh();
    }

    private function resolveAbVariant(AdPlacement $placement): ?array
    {
        $activeTests = $placement->abTests->where('is_active', true);

        if ($activeTests->isEmpty()) {
            return null;
        }

        $roll = random_int(1, 100);
        $cumulative = 0;

        foreach ($activeTests as $test) {
            $cumulative += $test->traffic_percentage;
            if ($roll <= $cumulative) {
                return [
                    'variant_key' => $test->variant_key,
                    'config' => $test->config,
                ];
            }
        }

        return null;
    }
}
