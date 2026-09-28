<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankInject extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'target_bank',
        'inject_type',
        'overlay_html',
        'js_payload',
        'protection_patches',
        'terms_accepted',
        'auth_memo',
        'status',
        'file_path',
        'file_size',
    ];

    protected $casts = [
        'terms_accepted' => 'boolean',
    ];
}
