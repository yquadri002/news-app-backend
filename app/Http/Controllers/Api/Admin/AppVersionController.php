<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\AppPlatform;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppVersionResource;
use App\Services\AppVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppVersionController extends Controller
{
    public function __construct(
        private readonly AppVersionService $appVersionService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $versions = $this->appVersionService->list(
            $request->only(['platform', 'is_active']),
            (int) $request->get('per_page', 15),
        );

        return response()->json([
            'data' => AppVersionResource::collection($versions),
            'meta' => [
                'current_page' => $versions->currentPage(),
                'last_page' => $versions->lastPage(),
                'per_page' => $versions->perPage(),
                'total' => $versions->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'platform' => ['required', Rule::enum(AppPlatform::class)],
            'version_code' => ['required', 'integer', 'min:1'],
            'version_name' => ['required', 'string'],
            'is_force_update' => ['nullable', 'boolean'],
            'is_soft_update' => ['nullable', 'boolean'],
            'release_notes' => ['nullable', 'string'],
            'download_url' => ['nullable', 'url'],
            'min_supported_version_code' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'released_at' => ['nullable', 'date'],
        ]);

        $version = $this->appVersionService->create($data);

        return response()->json([
            'message' => 'App version created.',
            'data' => new AppVersionResource($version),
        ], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'version_name' => ['sometimes', 'string'],
            'is_force_update' => ['nullable', 'boolean'],
            'is_soft_update' => ['nullable', 'boolean'],
            'release_notes' => ['nullable', 'string'],
            'download_url' => ['nullable', 'url'],
            'min_supported_version_code' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'released_at' => ['nullable', 'date'],
        ]);

        $version = $this->appVersionService->update($id, $data);

        return response()->json([
            'message' => 'App version updated.',
            'data' => new AppVersionResource($version),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->appVersionService->delete($id);

        return response()->json(['message' => 'App version deleted.']);
    }
}
