<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $fillable = ['title', 'description', 'category', 'video_url', 'thumbnail_url', 'content', 'created_by'];

    protected $casts = [
        'video_url' => 'string',
        'thumbnail_url' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
