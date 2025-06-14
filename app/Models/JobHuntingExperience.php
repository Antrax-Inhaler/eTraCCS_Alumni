<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobHuntingExperience extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel naming conventions)
    protected $table = 'job_hunting_experiences';

    // Fillable fields for mass assignment
    protected $fillable = [
        'user_id',
        'time_to_first_job',
        'difficulties',
        'other_difficulty',
        'job_source',
        'other_source',
        'starting_salary',
    ];

    // Cast the `difficulties` field as an array
    protected $casts = [
        'difficulties' => 'array',
    ];

    // Relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for time_to_first_job labels
    public function getTimeToFirstJobLabelAttribute()
    {
        $labels = [
            'less_than_1_month' => 'Less than 1 month',
            '1_to_3_months' => '1–3 months',
            '4_to_6_months' => '4–6 months',
            '7_to_12_months' => '7–12 months',
            'more_than_1_year' => 'More than a year',
            'never_employed' => 'Never employed',
        ];

        return $labels[$this->time_to_first_job] ?? 'Unknown';
    }

    // Accessor for job_source labels
    public function getJobSourceLabelAttribute()
    {
        $labels = [
            'online_portals' => 'Online job portals',
            'walk_in' => 'Walk-in application',
            'referral' => 'Referral',
            'university_fair' => 'University job fair',
            'social_media' => 'Social media',
            'government' => 'Government program',
            'other' => 'Other',
        ];

        return $labels[$this->job_source] ?? 'Unknown';
    }

    // Accessor for starting_salary labels
    public function getStartingSalaryLabelAttribute()
    {
        $labels = [
            'below_10k' => 'Below ₱10,000',
            '10k_15k' => '₱10,000–₱15,000',
            '15k_20k' => '₱15,001–₱20,000',
            '20k_30k' => '₱20,001–₱30,000',
            'above_30k' => 'Above ₱30,000',
        ];

        return $labels[$this->starting_salary] ?? 'Unknown';
    }
}