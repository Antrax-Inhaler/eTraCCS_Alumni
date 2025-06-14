<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentHistory extends Model
{
    use HasFactory;

    // Disable timestamps if not needed
    public $timestamps = true;

    // Define fillable fields
    protected $fillable = [
        'user_id', 'company_id', 'job_title', 'start_date', 'end_date',
        'employment_type', 'is_first_job', 'months_to_first_job', 
        'first_job_salary', 'job_source', 'other_source', 'difficulties',
        'other_difficulty', 'employment_status', 'employer_type',
        'current_salary', 'job_maintenance', 'has_promotion', 'has_awards',
        'awards_details', 'unemployment_reason', 'other_unemployment_reason',
        'competencies', 'curriculum_relevance', 'program_suggestions',
        'other_suggestion', 'nature_of_industry'
    ];

    protected $casts = [
        'is_first_job' => 'boolean',
        'has_promotion' => 'boolean',
        'has_awards' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Define relationships

    /**
     * An employment history belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * An employment history belongs to a company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}