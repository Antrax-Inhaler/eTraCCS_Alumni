<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
            'post_id' => 'nullable|exists:posts,id',
            'event_id' => 'nullable|exists:events,id',
            'job_id' => 'nullable|exists:job_postings,id',
        ];
    }
}
