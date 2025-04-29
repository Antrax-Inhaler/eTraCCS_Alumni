<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    // Fields that can be mass-assigned
    protected $fillable = [
        'user_id', // The ID of the user who reacted
        'content_item_id', // The ID of the post (nullable)
        'reaction_type', // The type of reaction (e.g., 1 = 👍, 2 = ❤️, etc.)
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contentItem()
    {
        return $this->belongsTo(ContentItem::class, 'content_item_id');
    }
}