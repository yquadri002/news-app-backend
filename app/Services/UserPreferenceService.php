<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPreference;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserPreferenceService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function registerOrUpdateDevice(array $data): User
    {
        $user = $this->userRepository->findByDeviceId($data['device_id']);

        if ($user) {
            $user->update(array_filter([
                'fcm_token' => $data['fcm_token'] ?? $user->fcm_token,
                'platform' => $data['platform'] ?? $user->platform,
                'app_version' => $data['app_version'] ?? $user->app_version,
                'last_active_at' => now(),
            ]));
        } else {
            $user = $this->userRepository->create([
                'device_id' => $data['device_id'],
                'fcm_token' => $data['fcm_token'] ?? null,
                'platform' => $data['platform'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'last_active_at' => now(),
            ]);
        }

        if (! $user->preferences) {
            $user->preferences()->create([
                'language' => $data['language'] ?? 'en',
            ]);
        }

        return $user->load('preferences');
    }

    public function getPreferences(User $user): UserPreference
    {
        return $user->preferences ?? $user->preferences()->create(['language' => 'en']);
    }

    public function updateInterests(User $user, array $interests): UserPreference
    {
        $prefs = $this->getPreferences($user);
        $prefs->update(['interests' => $interests]);

        return $prefs->fresh();
    }

    public function updateCategories(User $user, array $categoryIds): UserPreference
    {
        $prefs = $this->getPreferences($user);
        $prefs->update(['category_ids' => $categoryIds]);

        return $prefs->fresh();
    }

    public function updateSources(User $user, array $sourceIds): UserPreference
    {
        $prefs = $this->getPreferences($user);
        $prefs->update(['source_ids' => $sourceIds]);

        return $prefs->fresh();
    }

    public function updateLanguage(User $user, string $language): UserPreference
    {
        $user->update(['language' => $language]);
        $prefs = $this->getPreferences($user);
        $prefs->update(['language' => $language]);

        return $prefs->fresh();
    }

    public function updateLocation(User $user, string $location): UserPreference
    {
        $user->update(['location' => $location]);
        $prefs = $this->getPreferences($user);
        $prefs->update(['location' => $location]);

        return $prefs->fresh();
    }

    public function updateAll(User $user, array $data): UserPreference
    {
        $prefs = $this->getPreferences($user);

        if (isset($data['language'])) {
            $user->update(['language' => $data['language']]);
        }
        if (isset($data['location'])) {
            $user->update(['location' => $data['location']]);
        }

        $prefs->update(array_filter([
            'interests' => $data['interests'] ?? null,
            'category_ids' => $data['category_ids'] ?? null,
            'source_ids' => $data['source_ids'] ?? null,
            'language' => $data['language'] ?? null,
            'location' => $data['location'] ?? null,
            'notifications_enabled' => $data['notifications_enabled'] ?? null,
            'breaking_news_enabled' => $data['breaking_news_enabled'] ?? null,
        ], fn ($v) => $v !== null));

        return $prefs->fresh();
    }
}
