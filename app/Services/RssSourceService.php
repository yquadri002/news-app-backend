<?php

namespace App\Services;

use App\Enums\RssHealthStatus;
use App\Models\RssSource;
use App\Repositories\Contracts\RssSourceRepositoryInterface;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class RssSourceService
{
    public function __construct(
        private readonly RssSourceRepositoryInterface $rssSourceRepository,
    ) {
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->rssSourceRepository->paginate($perPage, $filters);
    }

    public function create(array $data): RssSource
    {
        $source = $this->rssSourceRepository->create($data);
        $this->validateSource($source->id);

        return $source->fresh(['category']);
    }

    public function update(int $id, array $data): RssSource
    {
        $source = $this->rssSourceRepository->update($id, $data);

        if (isset($data['url'])) {
            $this->validateSource($id);
        }

        return $source;
    }

    public function delete(int $id): bool
    {
        return $this->rssSourceRepository->delete($id);
    }

    public function updatePriority(int $id, int $priority): RssSource
    {
        return $this->rssSourceRepository->update($id, ['priority' => $priority]);
    }

    public function validateSource(int $id): array
    {
        $source = $this->rssSourceRepository->findOrFail($id);

        try {
            $response = Http::timeout(15)->get($source->url);

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status());
            }

            $xml = new SimpleXMLElement($response->body());
            $itemCount = count($xml->channel->item ?? $xml->entry ?? []);

            $this->rssSourceRepository->updateHealth($id, [
                'is_validated' => true,
                'last_validated_at' => now(),
                'health_status' => RssHealthStatus::Healthy,
                'error_count' => 0,
                'last_error' => null,
            ]);

            return [
                'valid' => true,
                'item_count' => $itemCount,
                'health_status' => RssHealthStatus::Healthy->value,
            ];
        } catch (\Throwable $e) {
            $errorCount = $source->error_count + 1;
            $healthStatus = $errorCount >= 5
                ? RssHealthStatus::Unhealthy
                : ($errorCount >= 2 ? RssHealthStatus::Degraded : RssHealthStatus::Unknown);

            $this->rssSourceRepository->updateHealth($id, [
                'is_validated' => false,
                'last_validated_at' => now(),
                'health_status' => $healthStatus,
                'error_count' => $errorCount,
                'last_error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => $e->getMessage(),
                'health_status' => $healthStatus->value,
            ];
        }
    }

    public function getHealthReport(): array
    {
        $sources = $this->rssSourceRepository->getActiveByPriority();
        $unhealthy = $this->rssSourceRepository->getUnhealthy();

        return [
            'total' => $sources->count(),
            'healthy' => $sources->where('health_status', RssHealthStatus::Healthy)->count(),
            'degraded' => $sources->where('health_status', RssHealthStatus::Degraded)->count(),
            'unhealthy' => $unhealthy->count(),
            'sources' => $sources,
        ];
    }
}
