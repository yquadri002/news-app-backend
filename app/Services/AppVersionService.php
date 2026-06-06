<?php

namespace App\Services;

use App\Models\AppVersion;
use App\Repositories\Contracts\AppVersionRepositoryInterface;

class AppVersionService
{
    public function __construct(
        private readonly AppVersionRepositoryInterface $appVersionRepository,
    ) {
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->appVersionRepository->paginate($perPage, $filters);
    }

    public function create(array $data): AppVersion
    {
        $data['released_at'] = $data['released_at'] ?? now();

        return $this->appVersionRepository->create($data);
    }

    public function update(int $id, array $data): AppVersion
    {
        return $this->appVersionRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->appVersionRepository->delete($id);
    }

    public function checkUpdate(string $platform, int $currentVersionCode): array
    {
        $result = $this->appVersionRepository->checkUpdateRequired($platform, $currentVersionCode);
        $latest = $result['latest_version'] ?? null;

        return [
            'update_available' => $result['update_available'],
            'force_update' => $result['force_update'],
            'soft_update' => $result['soft_update'],
            'version_code' => $latest?->version_code,
            'version_name' => $latest?->version_name,
            'release_notes' => $latest?->release_notes,
            'download_url' => $latest?->download_url,
        ];
    }
}
