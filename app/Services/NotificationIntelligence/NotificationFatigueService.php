<?php

namespace App\Services\NotificationIntelligence;

use App\Models\NotificationUserState;
use App\Models\User;
use Carbon\Carbon;

class NotificationFatigueService
{
    public function getOrCreateState(User $user): NotificationUserState
    {
        return NotificationUserState::firstOrCreate(
            ['user_id' => $user->id],
            [
                'timezone' => config('notification_intelligence.digest.timezone', 'UTC'),
                'quiet_hours_start' => config('notification_intelligence.fatigue.default_quiet_start'),
                'quiet_hours_end' => config('notification_intelligence.fatigue.default_quiet_end'),
                'daily_cap' => config('notification_intelligence.fatigue.default_daily_cap', 5),
                'sensitivity_score' => 0.5,
            ]
        );
    }

    public function canReceiveNotification(User $user): array
    {
        $state = $this->getOrCreateState($user);
        $this->resetDailyCountIfNeeded($state);

        $prefs = $user->preferences;
        if ($prefs && ! $prefs->notifications_enabled) {
            return ['allowed' => false, 'reason' => 'notifications_disabled'];
        }

        if ($state->cooldown_until && $state->cooldown_until->isFuture()) {
            return ['allowed' => false, 'reason' => 'cooldown_active', 'until' => $state->cooldown_until];
        }

        if ($state->daily_sent_count >= $state->daily_cap) {
            return ['allowed' => false, 'reason' => 'daily_cap_reached'];
        }

        if ($this->isQuietHours($state)) {
            return ['allowed' => false, 'reason' => 'quiet_hours'];
        }

        $sensitivityPenalty = $this->getSensitivityPenalty($state);
        if ($sensitivityPenalty > 0.8) {
            return ['allowed' => false, 'reason' => 'high_sensitivity'];
        }

        return [
            'allowed' => true,
            'sensitivity_penalty' => $sensitivityPenalty,
            'remaining_daily' => $state->daily_cap - $state->daily_sent_count,
        ];
    }

    public function recordSent(User $user): void
    {
        $state = $this->getOrCreateState($user);
        $this->resetDailyCountIfNeeded($state);

        $cooldownMinutes = config('notification_intelligence.fatigue.cooldown_minutes', 60);

        $state->update([
            'daily_sent_count' => $state->daily_sent_count + 1,
            'last_notification_at' => now(),
            'cooldown_until' => now()->addMinutes($cooldownMinutes),
            'total_received' => $state->total_received + 1,
        ]);
    }

    public function recordOpened(User $user): void
    {
        $state = $this->getOrCreateState($user);
        $state->increment('total_opened');
        $this->updateSensitivityScore($state);
    }

    public function getOptimalSendTime(User $user): Carbon
    {
        $state = $this->getOrCreateState($user);
        $timezone = $state->timezone ?? 'UTC';
        $now = now()->timezone($timezone);

        if ($this->isQuietHours($state)) {
            $quietEnd = $state->quiet_hours_end ?? '07:00';
            [$hour, $minute] = explode(':', $quietEnd);

            return $now->copy()->setTime((int) $hour, (int) $minute)->timezone('UTC');
        }

        $engagementHours = [8, 12, 18, 20];
        $currentHour = (int) $now->format('G');

        foreach ($engagementHours as $hour) {
            if ($hour > $currentHour) {
                return $now->copy()->setTime($hour, 0)->timezone('UTC');
            }
        }

        return $now->copy()->addDay()->setTime(8, 0)->timezone('UTC');
    }

    private function isQuietHours(NotificationUserState $state): bool
    {
        if (! $state->quiet_hours_start || ! $state->quiet_hours_end) {
            return false;
        }

        $tz = $state->timezone ?? 'UTC';
        $now = now()->timezone($tz)->format('H:i:s');
        $start = $state->quiet_hours_start;
        $end = $state->quiet_hours_end;

        if ($start > $end) {
            return $now >= $start || $now <= $end;
        }

        return $now >= $start && $now <= $end;
    }

    private function resetDailyCountIfNeeded(NotificationUserState $state): void
    {
        $today = now()->toDateString();
        if ($state->daily_count_reset_date?->toDateString() !== $today) {
            $state->update([
                'daily_sent_count' => 0,
                'daily_count_reset_date' => $today,
            ]);
        }
    }

    private function getSensitivityPenalty(NotificationUserState $state): float
    {
        if ($state->total_received === 0) {
            return 0;
        }

        $openRate = $state->total_opened / $state->total_received;

        return max(0, 1 - $openRate);
    }

    private function updateSensitivityScore(NotificationUserState $state): void
    {
        $openRate = $state->total_received > 0
            ? $state->total_opened / $state->total_received
            : 0.5;

        $state->update(['sensitivity_score' => round(1 - $openRate, 4)]);
    }
}
