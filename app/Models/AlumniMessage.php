<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumni_record_id',
        'user_id',
        'subject',
        'content',
        'method',
        'status',
        'scheduled_at',
        'sent_at',
        'error'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
    

    public function alumni()
    {
        return $this->belongsTo(AlumniRecord::class, 'alumni_record_id');
    }
        public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}