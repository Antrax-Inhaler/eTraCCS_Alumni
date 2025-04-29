<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalBackground extends Model
{
    protected $fillable = [
        'user_id', 'degree_earned', 'institution', 'campus', 'year_graduated',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
