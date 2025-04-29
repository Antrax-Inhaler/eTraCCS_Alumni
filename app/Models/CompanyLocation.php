<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyLocation extends Model
{
    protected $fillable = [
        'company_name', 'latitude', 'longitude', 'industry', 'country', 'city',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}