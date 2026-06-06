<?php

namespace App\Models;

use App\Enums\MonetizationSegment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMonetizationProfile extends Model
{
    protected $fillable = [
        'user_id',
        'segment',
        'lifetime_value',
        'total_ad_revenue',
        'total_subscription_revenue',
        'ad_impressions',
        'ad_clicks',
        'articles_read',
        'ad_sensitivity_score',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'segment' => MonetizationSegment::class,
            'lifetime_value' => 'decimal:4',
            'total_ad_revenue' => 'decimal:4',
            'total_subscription_revenue' => 'decimal:4',
            'ad_sensitivity_score' => 'decimal:4',
            'last_calculated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
