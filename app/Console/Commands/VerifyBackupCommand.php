<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class VerifyBackupCommand extends Command
{
    protected $signature = 'backup:verify {--max-age-hours=26}';

    protected $description = 'Verify that a recent database backup exists and is readable';

    public function handle(): int
    {
        if (! config('infrastructure.backup.enabled')) {
            $this->warn('Backups are disabled; verification skipped.');

            return self::SUCCESS;
        }

        $disk = config('infrastructure.backup.disk', 's3-backup');
        $maxAgeHours = (int) $this->option('max-age-hours');
        $cutoff = now()->subHours($maxAgeHours);

        $files = collect(Storage::disk($disk)->allFiles('backups'))
            ->filter(fn (string $file) => str_ends_with($file, '.sql.gz'))
            ->sort()
            ->values();

        if ($files->isEmpty()) {
            $this->error('No backup files found on remote storage.');

            return self::FAILURE;
        }

        $latest = $files->last();
        $size = Storage::disk($disk)->size($latest);

        if ($size <= 0) {
            $this->error("Latest backup '{$latest}' is empty.");

            return self::FAILURE;
        }

        if (! preg_match('/backup-(\d{4}-\d{2}-\d{2})-(\d{6})\.sql\.gz$/', basename($latest), $matches)) {
            $this->error("Latest backup '{$latest}' has an unexpected filename.");

            return self::FAILURE;
        }

        $backupTime = Carbon::createFromFormat('Y-m-d-His', "{$matches[1]}-{$matches[2]}");

        if ($backupTime->lt($cutoff)) {
            $this->error("Latest backup is older than {$maxAgeHours} hours ({$backupTime->toDateTimeString()}).");

            return self::FAILURE;
        }

        $this->info("Backup verification passed: {$latest} ({$size} bytes, {$backupTime->toDateTimeString()}).");

        return self::SUCCESS;
    }
}
