<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionalExam extends Model
{
    protected $fillable = [
        'user_id', 'exam_name', 'exam_date', 'license_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}