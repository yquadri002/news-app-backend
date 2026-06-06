<?php

namespace App\Http\Requests\Client;

use App\Enums\BehaviorEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BehaviorTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', Rule::enum(BehaviorEventType::class)],
            'article_id' => ['nullable', 'integer', 'exists:articles,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'rss_source_id' => ['nullable', 'integer', 'exists:rss_sources,id'],
            'read_time_seconds' => ['nullable', 'integer', 'min:0'],
            'scroll_depth_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'search_query' => ['nullable', 'string', 'max:500'],
            'session_id' => ['nullable', 'string'],
            'feed_type' => ['nullable', 'string'],
            'share_platform' => ['nullable', 'string'],
            'bookmarked' => ['nullable', 'boolean'],
        ];
    }
}
