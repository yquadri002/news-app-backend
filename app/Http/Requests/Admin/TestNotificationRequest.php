<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TestNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'required_without:fcm_token'],
            'fcm_token' => ['nullable', 'string', 'required_without:user_id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'article_id' => ['nullable', 'integer', 'exists:articles,id'],
        ];
    }
}
