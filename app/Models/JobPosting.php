<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Feedable;
class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'company_name', 'title', 'location', 'description',
    ];

    // Relationship with MediaFile
    public function mediaFiles()
    {
        return $this->hasMany(MediaFile::class, 'job_id');
    }
    public function reactions()
    {
        return $this->hasMany(Reaction::class, 'job_id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'job_id');
        
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}