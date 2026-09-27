<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BypassRule extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'rule_name',
        'target_package',
        'status',
    ];
}
