<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    // Disable timestamps if not needed
    public $timestamps = true;

    // Define fillable fields
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'industry',
    ];

    // Define relationships

    /**
     * A company has many employment histories.
     */
    public function employmentHistories()
    {
        return $this->hasMany(EmploymentHistory::class);
    }
    
}