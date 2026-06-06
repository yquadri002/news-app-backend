<?php

namespace App\Repositories;

use App\Models\AppVersion;
use App\Repositories\Contracts\AppVersionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class AppVersionRepository extends BaseRepository implements AppVersionRepositoryInterface
{
    public function __construct(AppVersion $model)
    {
        parent::__construct($model);
    }

    public function getLatestForPlatform(string $platform): ?AppVersion
    {
        return $this->query()
            ->where('platform', $platform)
            ->where('is_active', true)
            ->orderByDesc('version_code')
            ->first();
    }

    public function checkUpdateRequired(string $platform, int $currentVersionCode): array
    {
        $latest = $this->getLatestForPlatform($platform);

        if (! $latest) {
            return [
                'update_available' => false,
                'force_update' => false,
                'soft_update' => false,
            ];
        }

        $forceUpdate = $latest->is_force_update
            || ($latest->min_supported_version_code && $currentVersionCode < $latest->min_supported_version_code);

        $softUpdate = ! $forceUpdate && $latest->is_soft_update && $currentVersionCode < $latest->version_code;

        return [
            'update_available' => $currentVersionCode < $latest->version_code,
            'force_update' => $forceUpdate,
            'soft_update' => $softUpdate,
            'latest_version' => $latest,
        ];
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->orderByDesc('version_code');
    }
}
