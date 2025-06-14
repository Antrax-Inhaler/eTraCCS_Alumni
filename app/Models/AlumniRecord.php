<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniRecord extends Model
{
    use HasFactory;

    protected $table = 'alumni_records';

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_initial',
        'student_number',
        'degree_earned',
        'major',
        'campus',
        'year_graduated',
        'email',
    ];
    public function messages()
{
    return $this->hasMany(AlumniMessage::class);
}
public function latestMessage()
{
    return $this->hasOne(AlumniMessage::class)->latestOfMany();
}

}
