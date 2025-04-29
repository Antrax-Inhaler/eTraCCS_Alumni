<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
    ];
    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }
    public function mediaFiles()
{
    return $this->hasMany(MediaFile::class, 'post_id');
}
public function user()
{
    return $this->belongsTo(User::class);
}

public function comments()
{
    return $this->hasMany(Comment::class, 'post_id');
}
public function creator()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
