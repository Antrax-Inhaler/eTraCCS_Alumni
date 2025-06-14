<?php

// app/Models/JobMaintenanceMethod.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobMaintenanceMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}