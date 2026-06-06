<?php

namespace App\Http\Requests\Admin;

use App\Enums\RevenueAbTestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRevenueAbTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'test_type' => ['required', Rule::enum(RevenueAbTestType::class)],
            'variants' => ['required', 'array', 'min:2'],
            'variants.*.key' => ['required', 'string'],
            'variants.*.traffic_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'variants.*.config' => ['nullable', 'array'],
        ];
    }
}
