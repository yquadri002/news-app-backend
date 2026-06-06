<?php

namespace App\Console\Commands;

use App\Services\Infrastructure\AlertingService;
use Illuminate\Console\Command;

class MonitorInfrastructureCommand extends Command
{
    protected $signature = 'infrastructure:monitor';

    protected $description = 'Run infrastructure health checks and fire alerts';

    public function handle(AlertingService $alerting): int
    {
        $result = $alerting->runChecks();

        if ($result['status'] === 'disabled') {
            $this->info('Alerting is disabled.');

            return self::SUCCESS;
        }

        $count = $result['alerts_sent'] ?? 0;
        $this->info("Infrastructure monitor complete. {$count} alert(s) sent.");

        return self::SUCCESS;
    }
}
