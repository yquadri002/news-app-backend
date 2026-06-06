<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRssSourceRequest;
use App\Http\Resources\RssSourceResource;
use App\Services\RssSourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RssSourceController extends Controller
{
    public function __construct(
        private readonly RssSourceService $rssSourceService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $sources = $this->rssSourceService->list(
            $request->only(['search', 'is_active', 'health_status', 'category_id']),
            (int) $request->get('per_page', 15),
        );

        return response()->json([
            'data' => RssSourceResource::collection($sources),
            'meta' => [
                'current_page' => $sources->currentPage(),
                'last_page' => $sources->lastPage(),
                'per_page' => $sources->perPage(),
                'total' => $sources->total(),
            ],
        ]);
    }

    public function store(StoreRssSourceRequest $request): JsonResponse
    {
        $source = $this->rssSourceService->create($request->validated());

        return response()->json([
            'message' => 'RSS source created successfully.',
            'data' => new RssSourceResource($source),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $source = app(\App\Repositories\Contracts\RssSourceRepositoryInterface::class)->findOrFail($id);

        return response()->json(['data' => new RssSourceResource($source->load('category'))]);
    }

    public function update(StoreRssSourceRequest $request, int $id): JsonResponse
    {
        $source = $this->rssSourceService->update($id, $request->validated());

        return response()->json([
            'message' => 'RSS source updated successfully.',
            'data' => new RssSourceResource($source),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->rssSourceService->delete($id);

        return response()->json(['message' => 'RSS source removed successfully.']);
    }

    public function validateSource(int $id): JsonResponse
    {
        $result = $this->rssSourceService->validateSource($id);

        return response()->json(['message' => 'Validation completed.', 'data' => $result]);
    }

    public function health(): JsonResponse
    {
        return response()->json(['data' => $this->rssSourceService->getHealthReport()]);
    }

    public function updatePriority(int $id, Request $request): JsonResponse
    {
        $request->validate(['priority' => ['required', 'integer', 'min:0']]);
        $source = $this->rssSourceService->updatePriority($id, $request->integer('priority'));

        return response()->json([
            'message' => 'Priority updated.',
            'data' => new RssSourceResource($source),
        ]);
    }
}
