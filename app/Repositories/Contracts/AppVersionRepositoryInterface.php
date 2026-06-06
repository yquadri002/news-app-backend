<?php

namespace App\Repositories\Contracts;

use App\Models\AppVersion;

interface AppVersionRepositoryInterface extends BaseRepositoryInterface
{
    public function getLatestForPlatform(string $platform): ?AppVersion;

    public function checkUpdateRequired(string $platform, int $currentVersionCode): array;
}
