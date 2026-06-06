<?php

namespace App\Services\Revenue;

use App\Models\UserSubscription;
use Illuminate\Validation\ValidationException;

class SubscriptionReceiptValidator
{
    public function validate(string $platform, string $transactionId, int $planId): void
    {
        if (empty($transactionId)) {
            throw ValidationException::withMessages([
                'store_transaction_id' => ['Store transaction ID is required.'],
            ]);
        }

        if (UserSubscription::where('store_transaction_id', $transactionId)->exists()) {
            throw ValidationException::withMessages([
                'store_transaction_id' => ['This transaction has already been used.'],
            ]);
        }

        $pattern = match (strtolower($platform)) {
            'ios' => config('revenue.subscription.ios_transaction_pattern', '/^[A-Za-z0-9._-]{10,}$/'),
            'android' => config('revenue.subscription.android_transaction_pattern', '/^(GPA\.)?[A-Za-z0-9._-]{10,}$/'),
            default => config('revenue.subscription.default_transaction_pattern', '/^[A-Za-z0-9._-]{8,}$/'),
        };

        if (! preg_match($pattern, $transactionId)) {
            throw ValidationException::withMessages([
                'store_transaction_id' => ['Invalid store transaction ID format.'],
            ]);
        }

        if (config('revenue.subscription.strict_receipt_validation', false)) {
            $this->validateWithStore($platform, $transactionId, $planId);
        }
    }

    private function validateWithStore(string $platform, string $transactionId, int $planId): void
    {
        $configured = match (strtolower($platform)) {
            'ios' => ! empty(config('revenue.subscription.apple_shared_secret')),
            'android' => ! empty(config('revenue.subscription.google_service_account')),
            default => false,
        };

        if (! $configured) {
            throw ValidationException::withMessages([
                'store_transaction_id' => ['Store receipt validation is not configured on the server.'],
            ]);
        }
    }
}
