<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupVerifyTest extends TestCase
{
    public function test_backup_verify_passes_with_recent_backup(): void
    {
        Config::set('infrastructure.backup.enabled', true);
        Config::set('infrastructure.backup.disk', 's3-backup');

        Storage::fake('s3-backup');
        $filename = 'backups/'.now()->format('Y/m/d').'/backup-'.now()->format('Y-m-d-His').'.sql.gz';
        Storage::disk('s3-backup')->put($filename, 'gzip-content');

        $this->assertSame(0, Artisan::call('backup:verify'));
    }

    public function test_backup_verify_fails_when_no_backups_exist(): void
    {
        Config::set('infrastructure.backup.enabled', true);
        Config::set('infrastructure.backup.disk', 's3-backup');

        Storage::fake('s3-backup');

        $this->assertSame(1, Artisan::call('backup:verify'));
    }
}
