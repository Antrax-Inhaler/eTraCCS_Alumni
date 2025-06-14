<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    // Enable timestamps
    public $timestamps = true;

    // Define fillable fields
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'industry',
        // Add the new location fields
        'country',
        'city',
        'province',
        'region',
        'barangay'
    ];

    // Define casts for specific fields
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * A company has many employment histories.
     */
    public function employmentHistories()
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    /**
     * Get all users employed at this company
     */
    public function employees()
    {
        return $this->belongsToMany(User::class, 'employment_histories', 'company_id', 'user_id')
            ->withPivot('job_title', 'start_date', 'end_date', 'is_first_job', 'months_to_first_job', 'employment_type')
            ->withTimestamps();
    }

    /**
     * Scope for companies with location data
     */
    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    /**
     * Get full address as a string
     */
    public function getFullAddressAttribute()
    {
        $parts = [];
        if ($this->barangay) $parts[] = $this->barangay;
        if ($this->city) $parts[] = $this->city;
        if ($this->province) $parts[] = $this->province;
        if ($this->country) $parts[] = $this->country;

        return implode(', ', $parts);
    }

    /**
     * Get location as an array
     */
    public function getLocationArrayAttribute()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'country' => $this->country,
            'city' => $this->city,
            'province' => $this->province,
            'region' => $this->region,
            'barangay' => $this->barangay,
        ];
    }
}