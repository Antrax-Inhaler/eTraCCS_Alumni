<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
 public function rules(): array
    {
        return [
            'type' => 'required|in:event,post,job_posting',
            'title' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'tags' => 'nullable',
            'event_name' => 'nullable|string|max:255|required_if:type,event',
            'date' => 'nullable|date|required_if:type,event',
            'latitude' => 'nullable|numeric|between:-90,90|required_if:type,event|required_if:type,job_posting',
            'longitude' => 'nullable|numeric|between:-180,180|required_if:type,event|required_if:type,job_posting',
            'company_name' => 'nullable|string|max:255|required_if:type,job_posting',
            'job_title' => 'nullable|string|max:255|required_if:type,job_posting',
            'description' => 'nullable|string|required_if:type,job_posting',
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|mimes:jpg,jpeg,png,pdf,mp4,mov,avi|max:10240',
            'organizer_name' => 'nullable|string|max:255|required_if:type,event',
            'registration_link' => 'nullable|url|required_if:type,event',
            'application_deadline' => 'nullable|date|required_if:type,job_posting',
            'is_remote' => 'nullable|boolean',
            'privacy_setting' => 'required|in:public,private,batchmates,campus_only',
        ];
    }
}
