<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdAbTestResource;
use App\Http\Resources\AdPlacementResource;
use App\Services\AdPlacementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdPlacementController extends Controller
{
    public function __construct(
        private readonly AdPlacementService $adPlacementService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $placements = $this->adPlacementService->list(
            $request->only(['search', 'is_enabled']),
            (int) $request->get('per_page', 15),
        );

        return response()->json([
            'data' => AdPlacementResource::collection($placements),
            'meta' => [
                'current_page' => $placements->currentPage(),
                'last_page' => $placements->lastPage(),
                'per_page' => $placements->perPage(),
                'total' => $placements->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'placement_key' => ['required', 'string', 'unique:ad_placements,placement_key'],
            'format' => ['required', 'string'],
            'is_enabled' => ['nullable', 'boolean'],
            'frequency_cap' => ['nullable', 'integer', 'min:0'],
            'frequency_period_minutes' => ['nullable', 'integer', 'min:1'],
            'remote_config' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $placement = $this->adPlacementService->create($data);

        return response()->json([
            'message' => 'Ad placement created.',
            'data' => new AdPlacementResource($placement),
        ], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'format' => ['sometimes', 'string'],
            'is_enabled' => ['nullable', 'boolean'],
            'frequency_cap' => ['nullable', 'integer', 'min:0'],
            'frequency_period_minutes' => ['nullable', 'integer', 'min:1'],
            'remote_config' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $placement = $this->adPlacementService->update($id, $data);

        return response()->json([
            'message' => 'Ad placement updated.',
            'data' => new AdPlacementResource($placement),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->adPlacementService->delete($id);

        return response()->json(['message' => 'Ad placement deleted.']);
    }

    public function storeAbTest(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'variant_key' => ['required', 'string'],
            'traffic_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'config' => ['required', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $test = $this->adPlacementService->createAbTest($id, $data);

        return response()->json([
            'message' => 'A/B test created.',
            'data' => new AdAbTestResource($test),
        ], 201);
    }
}
