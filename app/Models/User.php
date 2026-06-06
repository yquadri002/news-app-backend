<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'device_id',
        'fcm_token',
        'language',
        'location',
        'platform',
        'app_version',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
        ];
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function interestProfile(): HasOne
    {
        return $this->hasOne(UserInterestProfile::class);
    }

    public function categoryScores(): HasMany
    {
        return $this->hasMany(UserCategoryScore::class);
    }

    public function sourceScores(): HasMany
    {
        return $this->hasMany(UserSourceScore::class);
    }

    public function topicScores(): HasMany
    {
        return $this->hasMany(UserTopicScore::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(UserBookmark::class);
    }

    public function behaviorEvents(): HasMany
    {
        return $this->hasMany(UserBehaviorEvent::class);
    }

    public function segmentMemberships(): HasMany
    {
        return $this->hasMany(UserSegmentMembership::class);
    }

    public function notificationState(): HasOne
    {
        return $this->hasOne(NotificationUserState::class);
    }

    public function notificationRecommendations(): HasMany
    {
        return $this->hasMany(NotificationRecommendation::class);
    }
}


