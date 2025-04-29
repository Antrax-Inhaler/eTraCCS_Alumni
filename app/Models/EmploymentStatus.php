<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentStatus extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'employment_status';

    // Fillable fields
    protected $fillable = [
        'user_id',
        'current_status',
        'first_job_duration',
        'occupation_classification',
        'company_name',
        'years_in_company',
        'job_relevance_to_degree',
    ];

    // Cast attributes for enums
    protected $casts = [
        'current_status' => 'string',
        'job_relevance_to_degree' => 'string',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}