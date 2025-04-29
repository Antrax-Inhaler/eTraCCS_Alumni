<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequiredCompetency extends Model
{
    // Table name (optional if it follows Laravel naming convention)
    protected $table = 'required_competencies';

    // Mass assignable attributes
    protected $fillable = [
        'industry',
        'job_title',
        'required_competencies',
    ];

    // Casts
    protected $casts = [
        'required_competencies' => 'array', // Automatically cast JSON to array
    ];
}
