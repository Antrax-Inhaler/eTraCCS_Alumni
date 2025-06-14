<?php

// app/Models/UserTheme.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme_template_id',
        'custom_colors',
        'is_custom'
    ];

    protected $casts = [
        'custom_colors' => 'array',
        'is_custom' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(ThemeTemplate::class, 'theme_template_id');
    }
}