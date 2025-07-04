<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobHuntingRequest extends FormRequest
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
            'time_to_first_job' => 'required|in:less_than_1_month,1_to_3_months,4_to_6_months,7_to_12_months,more_than_1_year,never_employed',
            'difficulties' => 'sometimes|string',
            'other_difficulty' => 'nullable|string|max:255',
            'job_source' => 'required|in:online_portals,walk_in,referral,university_fair,social_media,government,other',
            'other_source' => 'nullable|required_if:job_source,other|string|max:255',
            'starting_salary' => 'nullable|in:below_10k,10k_15k,15k_20k,20k_30k,above_30k'
        ];
    }
}
