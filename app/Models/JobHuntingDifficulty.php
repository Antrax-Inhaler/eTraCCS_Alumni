<?php

// app/Models/JobHuntingDifficulty.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobHuntingDifficulty extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}