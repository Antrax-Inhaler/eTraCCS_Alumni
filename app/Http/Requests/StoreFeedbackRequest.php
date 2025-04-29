<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ease_of_use_rating' => 'required|integer|between:1,5',
            'usefulness_rating' => 'required|integer|between:1,5',
            'satisfaction_rating' => 'required|integer|between:1,5',
            'intention_to_use_rating' => 'required|integer|between:1,5',
            'comments' => 'nullable|string|max:1000',
        ];
    }
}