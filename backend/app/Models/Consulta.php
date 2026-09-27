<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $fillable = ['user_id', 'tipo', 'documento', 'resultado', 'ip'];
    protected $casts = ['resultado' => 'array'];
}