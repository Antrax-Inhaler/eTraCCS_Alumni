<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserViewHistory extends Model
{
    use HasFactory;

    // Disable timestamps if not needed
    public $timestamps = true;

    // Define the table name explicitly
    protected $table = 'user_view_histories';

    // Define fillable fields
    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'viewed_at',
    ];

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}