<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CdnStorageService
{
    public function uploadImage(string $path, $contents, array $options = []): string
    {
        $disk = $this->getMediaDisk();
        $optimizedPath = $this->buildImagePath($path);

        Storage::disk($disk)->put($optimizedPath, $contents, array_merge([
            'visibility' => 'public',
            'CacheControl' => 'max-age=31536000, public',
        ], $options));

        return $this->url($optimizedPath);
    }

    public function url(string $path): string
    {
        if (config('infrastructure.cdn.enabled') && config('infrastructure.cdn.url')) {
            return rtrim(config('infrastructure.cdn.url'), '/').'/'.ltrim($path, '/');
        }

        return Storage::disk($this->getMediaDisk())->url($path);
    }

    public function temporaryUrl(string $path, ?int $ttl = null): string
    {
        $ttl ??= config('infrastructure.cdn.signed_url_ttl', 3600);

        return Storage::disk($this->getMediaDisk())->temporaryUrl(
            $path,
            now()->addSeconds($ttl)
        );
    }

    public function backup(string $localPath, string $remotePath): bool
    {
        $disk = config('infrastructure.backup.disk', 's3-backup');
        $contents = Storage::disk('local')->get($localPath);

        return Storage::disk($disk)->put($remotePath, $contents);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->getMediaDisk())->delete($path);
    }

    private function getMediaDisk(): string
    {
        return config('filesystems.media_disk', env('FILESYSTEM_DISK', 's3'));
    }

    private function buildImagePath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';

        return 'images/'.now()->format('Y/m/d').'/'.Str::uuid().'.'.$extension;
    }
}
