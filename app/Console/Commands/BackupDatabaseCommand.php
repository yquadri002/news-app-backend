<?php

namespace App\Console\Commands;

use App\Services\Infrastructure\CdnStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database {--disk=}';

    protected $description = 'Create and upload a database backup to remote storage';

    public function handle(CdnStorageService $storage): int
    {
        if (! config('infrastructure.backup.enabled')) {
            $this->warn('Backups are disabled.');

            return self::SUCCESS;
        }

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] !== 'mysql') {
            $this->error('Only MySQL backups are supported.');

            return self::FAILURE;
        }

        $filename = 'backup-'.now()->format('Y-m-d-His').'.sql.gz';
        $localPath = storage_path("app/backups/{$filename}");

        if (! is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0755, true);
        }

        $command = sprintf(
            'mysqldump -h%s -P%s -u%s -p%s %s | gzip > %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($localPath),
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Database backup failed.');

            return self::FAILURE;
        }

        $remotePath = 'backups/'.now()->format('Y/m/d').'/'.$filename;
        $disk = $this->option('disk') ?? config('infrastructure.backup.disk', 's3-backup');

        Storage::disk('local')->put("backups/{$filename}", file_get_contents($localPath));
        $storage->backup("backups/{$filename}", $remotePath);

        $this->info("Backup uploaded to {$disk}:{$remotePath}");
        $this->pruneOldBackups();

        return self::SUCCESS;
    }

    private function pruneOldBackups(): void
    {
        $retention = config('infrastructure.backup.retention_days', 30);
        $cutoff = now()->subDays($retention)->format('Y-m-d');

        $disk = config('infrastructure.backup.disk', 's3-backup');
        $files = Storage::disk($disk)->allFiles('backups');

        foreach ($files as $file) {
            if (str_contains($file, $cutoff) || $file < "backups/{$cutoff}") {
                Storage::disk($disk)->delete($file);
            }
        }
    }
}
