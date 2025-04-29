<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingAttended extends Model
{
    protected $table = 'trainings_attendeds';

    protected $fillable = [
        'user_id', 'training_name', 'institution', 'year_attended',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
