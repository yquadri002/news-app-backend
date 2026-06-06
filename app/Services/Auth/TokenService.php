<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Sanctum\PersonalAccessToken;

class TokenService
{
    public function issuePair(Authenticatable $user, string $deviceName): array
    {
        $accessMinutes = config('auth_tokens.access_ttl_minutes', 60);
        $refreshDays = config('auth_tokens.refresh_ttl_days', 30);
        $accessAbility = config('auth_tokens.abilities.access', 'access');
        $refreshAbility = config('auth_tokens.abilities.refresh', 'refresh');

        $accessToken = $user->createToken(
            "{$deviceName}-access",
            [$accessAbility],
            now()->addMinutes($accessMinutes),
        );

        $refreshToken = $user->createToken(
            "{$deviceName}-refresh",
            [$refreshAbility],
            now()->addDays($refreshDays),
        );

        return [
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $accessMinutes * 60,
            'refresh_expires_in' => $refreshDays * 86400,
        ];
    }

    public function refresh(Authenticatable $user, PersonalAccessToken $currentToken, string $deviceName): array
    {
        $user->tokens()->where('id', $currentToken->id)->delete();

        return $this->issuePair($user, $deviceName);
    }

    public function revokeAll(Authenticatable $user): void
    {
        $user->tokens()->delete();
    }
}
