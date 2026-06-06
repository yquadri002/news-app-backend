<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class RecommendationFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recommendation_log_id' => ['nullable', 'integer', 'exists:recommendation_logs,id'],
            'article_id' => ['required', 'integer', 'exists:articles,id'],
            'session_id' => ['nullable', 'string'],
            'feed_type' => ['nullable', 'string'],
            'was_clicked' => ['nullable', 'boolean'],
            'was_read' => ['nullable', 'boolean'],
            'read_time_seconds' => ['nullable', 'integer', 'min:0'],
            'scroll_depth_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'bookmarked' => ['nullable', 'boolean'],
            'shared' => ['nullable', 'boolean'],
            'share_platform' => ['nullable', 'string'],
        ];
    }
}
