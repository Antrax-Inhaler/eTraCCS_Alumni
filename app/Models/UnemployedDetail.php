<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnemployedDetail extends Model
{
    use HasFactory;

    protected $table = 'unemployed_details';

    protected $fillable = [
        'user_id',
        'unemployment_reasons',
        'other_unemployment_reason',
        'has_awards',
        'awards_details',
    ];

    protected $casts = [
        'has_awards' => 'boolean',
    ];

    // Custom accessor for unemployment_reasons
    public function getUnemploymentReasonsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    // Custom mutator for unemployment_reasons
    public function setUnemploymentReasonsAttribute($value)
    {
        $this->attributes['unemployment_reasons'] = is_array($value) 
            ? implode(',', $value) 
            : $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUnemploymentReasonsLabelsAttribute()
    {
        $labels = [
            'seeking' => 'Still seeking employment',
            'studying' => 'Pursuing further studies',
            'family_responsibilities' => 'Family responsibilities',
            'health_issues' => 'Health issues',
            'not_interested' => 'Not interested in employment',
            'other' => 'Other',
        ];

        $reasons = $this->unemployment_reasons;
        return collect($reasons)->map(function ($reason) use ($labels) {
            return $labels[$reason] ?? 'Unknown';
        })->toArray();
    }
}