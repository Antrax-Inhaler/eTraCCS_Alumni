<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'content', 'parent_id', 'content_item_id'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contentItem()
    {
        return $this->belongsTo(ContentItem::class, 'content_item_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user');
    }
}