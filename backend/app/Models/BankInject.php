<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankInject extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'inject_type',
        'status',
    ];
}
