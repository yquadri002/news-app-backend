<?php

namespace App\Services\Infrastructure;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertingService
{
    public function __construct(
        private readonly MonitoringService $monitoring,
    ) {
    }

    public function runChecks(): array
    {
        if (! config('infrastructure.alerting.enabled')) {
            return ['status' => 'disabled'];
        }

        $alerts = [];
        $thresholds = config('infrastructure.alerting.thresholds');

        $cpu = $this->getCpuUsage();
        if ($cpu > $thresholds['cpu_percent']) {
            $alerts[] = $this->fire('cpu_high', "CPU usage at {$cpu}%", ['cpu' => $cpu]);
        }

        $memory = $this->getMemoryUsage();
        if ($memory > $thresholds['memory_percent']) {
            $alerts[] = $this->fire('memory_high', "Memory usage at {$memory}%", ['memory' => $memory]);
        }

        $queueMetrics = $this->monitoring->getQueueMetrics();
        foreach ($queueMetrics as $queue => $data) {
            if ($data['size'] > $thresholds['queue_backlog']) {
                $alerts[] = $this->fire('queue_backlog', "Queue {$queue} backlog: {$data['size']}", $data);
            }
        }

        $dbUsage = $this->getDatabaseConnectionUsage();
        if ($dbUsage > $thresholds['db_connections_percent']) {
            $alerts[] = $this->fire('db_connections', "DB connections at {$dbUsage}%", ['usage' => $dbUsage]);
        }

        $failureRate = $this->getNotificationFailureRate();
        if ($failureRate > $thresholds['notification_failure_rate']) {
            $alerts[] = $this->fire('notification_failures', "Notification failure rate: ".round($failureRate * 100, 1).'%', [
                'rate' => $failureRate,
            ]);
        }

        return ['alerts_sent' => count($alerts), 'alerts' => $alerts];
    }

    public function fire(string $type, string $message, array $context = []): array
    {
        Log::warning("Infrastructure alert [{$type}]: {$message}", $context);

        $this->notifySlack($type, $message, $context);
        $this->notifyEmail($type, $message, $context);

        return ['type' => $type, 'message' => $message, 'context' => $context];
    }

    private function notifySlack(string $type, string $message, array $context): void
    {
        $webhook = config('infrastructure.alerting.slack_webhook');
        if (! $webhook) {
            return;
        }

        try {
            Http::post($webhook, [
                'text' => ":warning: *NewsHub Pro Alert* [{$type}]\n{$message}",
                'attachments' => [['text' => json_encode($context)]],
            ]);
        } catch (\Throwable $e) {
            Log::error('Slack alert failed', ['error' => $e->getMessage()]);
        }
    }

    private function notifyEmail(string $type, string $message, array $context): void
    {
        $email = config('infrastructure.alerting.email');
        if (! $email) {
            return;
        }

        try {
            Mail::raw("Alert [{$type}]: {$message}\n\n".json_encode($context, JSON_PRETTY_PRINT), function ($mail) use ($email, $type) {
                $mail->to($email)->subject("NewsHub Pro Alert: {$type}");
            });
        } catch (\Throwable $e) {
            Log::error('Email alert failed', ['error' => $e->getMessage()]);
        }
    }

    private function getCpuUsage(): float
    {
        if (! function_exists('sys_getloadavg')) {
            return 0;
        }
        $load = sys_getloadavg();

        return round(($load[0] ?? 0) * 100 / max(1, (int) shell_exec('nproc') ?: 1), 1);
    }

    private function getMemoryUsage(): float
    {
        $meminfo = @file_get_contents('/proc/meminfo');
        if (! $meminfo) {
            return 0;
        }
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
        if (empty($total[1]) || empty($available[1])) {
            return 0;
        }

        return round((1 - ($available[1] / $total[1])) * 100, 1);
    }

    private function getDatabaseConnectionUsage(): float
    {
        try {
            $max = (int) (DB::select('SHOW VARIABLES LIKE "max_connections"')[0]->Value ?? 100);
            $current = (int) (DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0);

            return $max > 0 ? round(($current / $max) * 100, 1) : 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    private function getNotificationFailureRate(): float
    {
        $recent = Notification::where('created_at', '>=', now()->subHour())->get();
        if ($recent->isEmpty()) {
            return 0;
        }

        $total = $recent->sum('total_recipients');
        $failed = $recent->sum('failed_count');

        return $total > 0 ? $failed / $total : 0;
    }
}
