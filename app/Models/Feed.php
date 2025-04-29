<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'score',
        'created_at'
    ];

    // Define a polymorphic relationship to retrieve the related entity (job, post, event)
    public function feedable()
    {
        return $this->morphTo();
    }
}