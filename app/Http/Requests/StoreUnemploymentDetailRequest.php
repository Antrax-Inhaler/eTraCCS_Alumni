<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnemploymentDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unemployment_reasons' => 'required|array',
            'unemployment_reasons.*' => 'string|in:seeking,studying,family_responsibilities,health_issues,not_interested,other',
            'other_unemployment_reason' => 'nullable|required_if:unemployment_reasons,other|string|max:255',
            'has_awards' => 'required|boolean',
            'awards_details' => 'nullable|required_if:has_awards,true|string|max:1000'
        ];
    }
}
