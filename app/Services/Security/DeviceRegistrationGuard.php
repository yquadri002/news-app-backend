<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class DeviceRegistrationGuard
{
    public function assertAllowed(string $deviceId, ?string $ip): void
    {
        $ip = $ip ?? 'unknown';
        $perMinuteKey = "device-register:minute:{$ip}";
        $perDayKey = "device-register:day:{$ip}";
        $deviceKey = "device-register:device:{$deviceId}";

        $minuteLimit = (int) config('infrastructure.device_registration.per_minute', 5);
        $dailyLimit = (int) config('infrastructure.device_registration.per_day', 20);

        if (RateLimiter::tooManyAttempts($perMinuteKey, $minuteLimit)) {
            throw ValidationException::withMessages([
                'device_id' => ['Too many registration attempts. Please try again later.'],
            ]);
        }

        if (RateLimiter::tooManyAttempts($perDayKey, $dailyLimit)) {
            throw ValidationException::withMessages([
                'device_id' => ['Daily registration limit reached for this network.'],
            ]);
        }

        if (! User::where('device_id', $deviceId)->exists()) {
            RateLimiter::hit($perMinuteKey, 60);
            RateLimiter::hit($perDayKey, 86400);
        }

        RateLimiter::hit($deviceKey, 60);
    }
}
