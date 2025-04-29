<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'sample_tb';

    // Specify the primary key (optional, as 'id' is the default)
    protected $primaryKey = 'id';

    // Specify which attributes are mass assignable
    protected $fillable = [
        'name',
        'age',
        'email',
    ];

    // Disable timestamps if you don't want Laravel to manage them automatically
    public $timestamps = true; // Set to false if you don't want `created_at` and `updated_at`
}