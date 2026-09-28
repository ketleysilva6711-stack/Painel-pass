<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $fillable = ['name', 'description', 'price', 'duration_days', 'icon', 'active'];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'duration_days' => 'integer',
    ];
}
