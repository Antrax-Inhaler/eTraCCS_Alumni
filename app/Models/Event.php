<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Feedable;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'event_name', 'date', 'location',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with MediaFile
    public function mediaFiles()
    {
        return $this->hasMany(MediaFile::class, 'event_id');
    }
    public function reactions()
    {
        return $this->hasMany(Reaction::class, 'event_id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'event_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}