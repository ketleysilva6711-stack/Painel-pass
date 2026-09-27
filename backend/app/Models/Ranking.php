<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    protected $fillable = ['user_id', 'points', 'position'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}