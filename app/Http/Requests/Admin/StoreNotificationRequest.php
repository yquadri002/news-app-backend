<?php

namespace App\Http\Requests\Admin;

use App\Enums\NotificationTargetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'image_url' => ['nullable', 'url'],
            'action_type' => ['nullable', 'string'],
            'action_data' => ['nullable', 'array'],
            'target_type' => ['required', Rule::enum(NotificationTargetType::class)],
            'target_ids' => ['nullable', 'array'],
            'target_ids.*' => ['integer'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
