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
        'user_id',
        'company_id',
        'job_title',
        'start_date',
        'end_date',
        'employment_status',
        'is_job_related_to_degree',
        'months_to_first_job',
        'is_first_job',
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