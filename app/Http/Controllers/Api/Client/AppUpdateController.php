<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\AdPlacementService;
use App\Services\AppVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppUpdateController extends Controller
{
    public function __construct(
        private readonly AppVersionService $appVersionService,
        private readonly AdPlacementService $adPlacementService,
    ) {
    }

    public function checkUpdate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'platform' => ['required', 'string', 'in:android,ios'],
            'version_code' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json([
            'data' => $this->appVersionService->checkUpdate(
                $data['platform'],
                $data['version_code'],
            ),
        ]);
    }

    public function remoteConfig(): JsonResponse
    {
        return response()->json([
            'data' => [
                'ads' => $this->adPlacementService->getRemoteConfig(),
            ],
        ]);
    }
}
