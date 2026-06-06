<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\DeviceRegisterRequest;
use App\Http\Resources\UserPreferenceResource;
use App\Services\Auth\TokenService;
use App\Services\Security\DeviceRegistrationGuard;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(
        private readonly UserPreferenceService $userPreferenceService,
        private readonly TokenService $tokenService,
        private readonly DeviceRegistrationGuard $registrationGuard,
    ) {
    }

    public function register(DeviceRegisterRequest $request): JsonResponse
    {
        $this->registrationGuard->assertAllowed(
            $request->input('device_id'),
            $request->ip(),
        );

        $user = $this->userPreferenceService->registerOrUpdateDevice($request->validated());
        $tokens = $this->tokenService->issuePair($user, 'mobile-device');

        return response()->json([
            'message' => 'Device registered successfully.',
            'data' => [
                'user_id' => $user->id,
                'token' => $tokens['access_token'],
                'tokens' => $tokens,
                'preferences' => new UserPreferenceResource($user->preferences),
            ],
        ], 201);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = $this->tokenService->refresh(
            $user,
            $user->currentAccessToken(),
            'mobile-device',
        );

        return response()->json([
            'message' => 'Token refreshed.',
            'data' => [
                'user_id' => $user->id,
                'token' => $tokens['access_token'],
                'tokens' => $tokens,
            ],
        ]);
    }
}
