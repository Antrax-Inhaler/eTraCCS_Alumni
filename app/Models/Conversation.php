<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'name', 'year_graduated', 'campus','profile_picture', 'created_by',
    ];

    // Relationships
    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
public function latestMessage()
{
    return $this->hasOne(Message::class)->latestOfMany();
}

}