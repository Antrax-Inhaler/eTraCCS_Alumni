<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competency extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'competencies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'competency_name',
        'description',
        'usefulness_score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'usefulness_score' => 'decimal:2', // Cast usefulness_score as a decimal with 2 decimal places
    ];

    /**
     * Get the user that owns the competency.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}