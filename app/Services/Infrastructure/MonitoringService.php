<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class MonitoringService
{
    public function getHealthStatus(): array
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => config('infrastructure.health.check_database') ? $this->checkDatabase() : ['status' => 'skipped'],
            'redis' => config('infrastructure.health.check_redis') ? $this->checkRedis() : ['status' => 'skipped'],
            'queue' => config('infrastructure.health.check_queue') ? $this->checkQueue() : ['status' => 'skipped'],
            'storage' => config('infrastructure.health.check_storage') ? $this->checkStorage() : ['status' => 'skipped'],
        ];

        $healthy = collect($checks)->every(fn ($c) => in_array($c['status'], ['ok', 'skipped']));

        return [
            'status' => $healthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'metrics' => $this->getApplicationMetrics(),
        ];
    }

    public function getApplicationMetrics(): array
    {
        return [
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'queue_size' => $this->getQueueSize(),
            'cache_hit_ratio' => Cache::get('metrics:cache_hit_ratio', 0),
            'horizon_status' => $this->getHorizonStatus(),
        ];
    }

    public function getQueueMetrics(): array
    {
        $queues = ['high', 'notifications', 'rss', 'ingestion', 'analytics', 'recommendations', 'default'];
        $metrics = [];

        foreach ($queues as $queue) {
            $metrics[$queue] = [
                'size' => $this->getQueueLength($queue),
                'threshold' => config('infrastructure.alerting.thresholds.queue_backlog', 1000),
            ];
        }

        return $metrics;
    }

    private function checkApp(): array
    {
        return ['status' => 'ok', 'version' => config('app.version', '1.0.0')];
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return ['status' => 'ok', 'latency_ms' => $latency];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $latency = round((microtime(true) - $start) * 1000, 2);

            return ['status' => 'ok', 'latency_ms' => $latency];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $size = $this->getQueueSize();
            $threshold = config('infrastructure.alerting.thresholds.queue_backlog', 1000);

            return [
                'status' => $size > $threshold ? 'warning' : 'ok',
                'pending_jobs' => $size,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            Storage::disk($disk)->exists('/') || Storage::disk($disk)->directories();

            return ['status' => 'ok', 'disk' => $disk];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function getQueueSize(): int
    {
        $total = 0;
        foreach (['high', 'notifications', 'rss', 'ingestion', 'analytics', 'recommendations', 'default'] as $queue) {
            $total += $this->getQueueLength($queue);
        }

        return $total;
    }

    private function getQueueLength(string $queue): int
    {
        try {
            return Queue::size($queue);
        } catch (\Throwable) {
            try {
                return (int) Redis::llen("queues:{$queue}");
            } catch (\Throwable) {
                return 0;
            }
        }
    }

    private function getHorizonStatus(): string
    {
        try {
            $masters = Redis::connection('default')->zrange('horizon:masters', 0, -1);

            return count($masters) > 0 ? 'running' : 'stopped';
        } catch (\Throwable) {
            return 'unknown';
        }
    }
}
