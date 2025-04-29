<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    // Disable timestamps if you don't want Laravel to manage them automatically
    public $timestamps = true;

    // Define the table name explicitly (optional if the table name matches the model name)
    protected $table = 'follows';

    // Define fillable fields (if needed)
    protected $fillable = [
        'follower_id',
        'followed_id',
    ];

    // Relationship: Get the user who is following
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    // Relationship: Get the user being followed
    public function followed()
    {
        return $this->belongsTo(User::class, 'followed_id');
    }
}