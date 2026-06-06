<?php

namespace App\Http\Controllers;

use App\Services\Infrastructure\MonitoringService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __construct(
        private readonly MonitoringService $monitoring,
    ) {
    }

    public function index(): JsonResponse
    {
        $health = $this->monitoring->getHealthStatus();
        $code = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $code);
    }

    public function metrics(): JsonResponse
    {
        return response()->json([
            'application' => $this->monitoring->getApplicationMetrics(),
            'queues' => $this->monitoring->getQueueMetrics(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
