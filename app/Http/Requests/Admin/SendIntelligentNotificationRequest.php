<?php

namespace App\Http\Requests\Admin;

use App\Enums\DigestType;
use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendIntelligentNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(NotificationType::class)],
            'recommendation_id' => ['nullable', 'integer', 'exists:notification_recommendations,id'],
            'article_id' => ['nullable', 'integer', 'exists:articles,id'],
            'segment_id' => ['nullable', 'integer', 'exists:user_segments,id'],
            'digest_type' => ['nullable', Rule::enum(DigestType::class)],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
