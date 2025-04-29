<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    use HasFactory;

    // Add the new columns to the fillable array
    protected $fillable = [
        'type', 
        'user_id', 
        'title', 
        'body', 
        'event_name', 
        'date', 
        'latitude', 
        'longitude',
        'company_name', 
        'job_title', 
        'description',
        'organizer_name', // For events: Organizer's name
        'registration_link', // For events: Registration link
        'salary_range', // For job postings: Salary range
        'application_deadline', // For job postings: Application deadline
        'tags', // For posts: Tags (JSON)
        'is_remote', // For job postings: Is the job remote?
        'privacy_setting',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'content_item_id');
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class, 'content_item_id');
    }

    public function mediaFiles()
    {
        return $this->hasMany(MediaFile::class, 'content_item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}