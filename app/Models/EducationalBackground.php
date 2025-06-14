<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalBackground extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'degree_earned',
        'degree_type',
        'institution',
        'from_mindoro_state',
        'college',
        'campus_id',
        'year_graduated',
        'currently_studying',
        'is_primary',
        'is_graduate_study',
        'reason_for_study',
        'other_reason'
    ];

    protected $casts = [
        'from_mindoro_state' => 'boolean',
        'is_primary' => 'boolean',
        'currently_studying' => 'boolean',
        'is_graduate_study' => 'boolean'
    ];

    // Reason for study options
    const REASONS = [
        'career_advancement' => 'Career advancement',
        'promotion_requirement' => 'Promotion requirement',
        'academic_interest' => 'Academic interest',
        'job_requirement' => 'Job requirement',
        'other' => 'Other'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFromMindoroState($query)
    {
        return $query->where('from_mindoro_state', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeBachelor($query)
    {
        return $query->where('degree_type', 'bachelor');
    }

    public function scopeGraduateStudies($query)
    {
        return $query->where('is_graduate_study', true);
    }

    public function getReasonForStudyAttribute($value)
    {
        return $value ? self::REASONS[$value] ?? $value : null;
    }
        public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}