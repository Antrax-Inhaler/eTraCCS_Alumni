<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployabilityIndex extends Model
{
    protected $table = 'employability_index';

    protected $fillable = [
        'user_id',
        'employability_score',
        'competencies_learned',
    ];

    protected $casts = [
        'employability_score' => 'float',
    ];

    // Relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
