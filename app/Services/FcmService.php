<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class FcmService
{
    private Messaging|false|null $messaging = null;

    public function sendToUsers(Notification $notification, Collection $users): array
    {
        $delivered = 0;
        $failed = 0;

        foreach ($users as $user) {
            if (! $user->fcm_token) {
                $failed++;

                continue;
            }

            try {
                $this->sendToToken($user->fcm_token, $notification);
                $delivered++;

                $notification->deliveries()->create([
                    'user_id' => $user->id,
                    'fcm_token' => $user->fcm_token,
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
            } catch (\Throwable $e) {
                $failed++;
                Log::error('FCM delivery failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                $notification->deliveries()->create([
                    'user_id' => $user->id,
                    'fcm_token' => $user->fcm_token,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return ['delivered' => $delivered, 'failed' => $failed];
    }

    public function sendToToken(string $token, Notification $notification): void
    {
        $messaging = $this->messaging();

        if (! $messaging) {
            Log::info('FCM mock send', ['token' => $token, 'title' => $notification->title]);

            return;
        }

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(FcmNotification::create($notification->title, $notification->body))
            ->withData([
                'action_type' => $notification->action_type ?? '',
                'action_data' => json_encode($notification->action_data ?? []),
                'notification_id' => (string) $notification->id,
            ]);

        $messaging->send($message);
    }

    private function messaging(): ?Messaging
    {
        if ($this->messaging === false) {
            return null;
        }

        if ($this->messaging instanceof Messaging) {
            return $this->messaging;
        }

        $credentialsPath = config('firebase.projects.app.credentials');

        if (! $credentialsPath || ! is_readable($credentialsPath)) {
            $this->messaging = false;

            return null;
        }

        try {
            $this->messaging = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->createMessaging();

            return $this->messaging;
        } catch (\Throwable $e) {
            Log::warning('Firebase messaging unavailable', ['error' => $e->getMessage()]);
            $this->messaging = false;

            return null;
        }
    }
}
