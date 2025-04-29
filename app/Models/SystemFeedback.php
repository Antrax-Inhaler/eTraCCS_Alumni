<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemFeedback extends Model
{
    protected $table = 'system_feedback';

    protected $fillable = [
        'user_id',
        'ease_of_use_rating',
        'usefulness_rating',
        'satisfaction_rating',
        'intention_to_use_rating',
        'comments',
        'last_feedback_at',
        'opt_out_feedback',
    ];

    /**
     * Relationship to the User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
