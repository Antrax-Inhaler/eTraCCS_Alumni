<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_item_id',
        'file_type',
        'file_path',
    ];
    public function contentItem()
    {
        return $this->belongsTo(ContentItem::class, 'content_item_id');
    }

    
}
