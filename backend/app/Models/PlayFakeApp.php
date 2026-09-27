<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayFakeApp extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'package_name',
        'slug',
        'status',
    ];
}
