<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BsitProgramSuggestion extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel naming conventions)
    protected $table = 'bsit_program_suggestions';

    // Fillable fields for mass assignment
    protected $fillable = [
        'user_id',
        'suggestion_type',
        'description',
    ];

    // Relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for suggestion type labels
    public function getSuggestionTypeLabelAttribute()
    {
        $labels = [
            'industry_aligned_subjects' => 'More industry-aligned subjects',
            'hands_on_projects' => 'More hands-on projects',
            'updated_tools' => 'Updated tools and technologies',
            'stronger_internship' => 'Stronger internship/OJT programs',
            'cert_prep' => 'Certification preparation',
            'career_services' => 'Career guidance and placement services',
            'other' => 'Other',
        ];

        return $labels[$this->suggestion_type] ?? 'Unknown';
    }
}