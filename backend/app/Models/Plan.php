<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['name', 'slug', 'price', 'limits', 'features', 'active'];

    protected $casts = [
        'limits' => 'array',
        'features' => 'array',
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];
}