<?php

namespace Database\Seeders;

use App\Enums\AppPlatform;
use App\Models\AppVersion;
use Illuminate\Database\Seeder;

class AppVersionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([AppPlatform::Android, AppPlatform::Ios] as $platform) {
            AppVersion::updateOrCreate(
                [
                    'platform' => $platform,
                    'version_code' => 100,
                ],
                [
                    'version_name' => '1.0.0',
                    'is_force_update' => false,
                    'is_soft_update' => false,
                    'release_notes' => 'Initial release of NewsHub Pro.',
                    'is_active' => true,
                    'released_at' => now(),
                ]
            );
        }
    }
}
