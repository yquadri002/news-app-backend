<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\DeviceRegisterRequest;
use App\Http\Resources\UserPreferenceResource;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    public function __construct(
        private readonly UserPreferenceService $userPreferenceService,
    ) {
    }

    public function register(DeviceRegisterRequest $request): JsonResponse
    {
        $user = $this->userPreferenceService->registerOrUpdateDevice($request->validated());
        $token = $user->createToken('mobile-device')->plainTextToken;

        return response()->json([
            'message' => 'Device registered successfully.',
            'data' => [
                'user_id' => $user->id,
                'token' => $token,
                'preferences' => new UserPreferenceResource($user->preferences),
            ],
        ], 201);
    }
}
