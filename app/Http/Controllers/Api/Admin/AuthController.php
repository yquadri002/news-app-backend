<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\PasswordResetConfirmRequest;
use App\Http\Requests\Admin\PasswordResetRequest;
use App\Http\Resources\AdminResource;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AdminAuthService $authService,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password,
            $request->device_name ?? 'admin-panel',
        );

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'admin' => new AdminResource($result['admin']),
                'token' => $result['token'],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new AdminResource($request->user()->load('role')),
        ]);
    }

    public function forgotPassword(PasswordResetRequest $request): JsonResponse
    {
        $message = $this->authService->sendPasswordResetLink($request->email);

        return response()->json(['message' => $message]);
    }

    public function resetPassword(PasswordResetConfirmRequest $request): JsonResponse
    {
        $message = $this->authService->resetPassword(
            $request->email,
            $request->token,
            $request->password,
        );

        return response()->json(['message' => $message]);
    }
}
