<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'source_ids' => ['nullable', 'array'],
            'source_ids.*' => ['integer', 'exists:rss_sources,id'],
            'language' => ['nullable', 'string', 'max:10'],
            'location' => ['nullable', 'string', 'max:255'],
            'notifications_enabled' => ['nullable', 'boolean'],
            'breaking_news_enabled' => ['nullable', 'boolean'],
        ];
    }
}
