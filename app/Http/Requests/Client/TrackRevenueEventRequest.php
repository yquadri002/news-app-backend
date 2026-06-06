<?php

namespace App\Http\Requests\Client;

use App\Enums\AdNetwork;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackRevenueEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', Rule::in(['impression', 'click'])],
            'ad_network' => ['nullable', Rule::enum(AdNetwork::class)],
            'ad_placement_id' => ['nullable', 'integer', 'exists:ad_placements,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'country' => ['nullable', 'string', 'max:5'],
            'ad_format' => ['nullable', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
