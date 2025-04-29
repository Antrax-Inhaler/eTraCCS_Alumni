<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniLocation extends Model
{
    protected $fillable = [
        'user_id', 'latitude', 'longitude', 'country', 'city',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
