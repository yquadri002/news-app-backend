<?php

namespace App\Services;

use App\Models\Admin;
use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Services\Auth\TokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    public function __construct(
        private readonly AdminRepositoryInterface $adminRepository,
        private readonly TokenService $tokenService,
    ) {
    }

    public function login(string $email, string $password, string $deviceName = 'admin-panel'): array
    {
        $admin = $this->adminRepository->findByEmail($email);

        if (! $admin || ! Hash::check($password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $admin->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account has been deactivated.'],
            ]);
        }

        $this->adminRepository->updateLastLogin($admin);
        $tokens = $this->tokenService->issuePair($admin, $deviceName);

        return [
            'admin' => $admin->load('role'),
            'token' => $tokens['access_token'],
            'tokens' => $tokens,
        ];
    }

    public function refresh(Admin $admin, string $deviceName = 'admin-panel'): array
    {
        $tokens = $this->tokenService->refresh(
            $admin,
            $admin->currentAccessToken(),
            $deviceName,
        );

        return [
            'admin' => $admin->load('role'),
            'token' => $tokens['access_token'],
            'tokens' => $tokens,
        ];
    }

    public function logout(Admin $admin): void
    {
        $this->tokenService->revokeAll($admin);
    }

    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::broker('admins')->sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    public function resetPassword(string $email, string $token, string $password): string
    {
        $status = Password::broker('admins')->reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function (Admin $admin, string $password) {
                $admin->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }
}
