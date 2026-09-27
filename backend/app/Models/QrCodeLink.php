<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCodeLink extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'target_url',
        'scans',
    ];
}
