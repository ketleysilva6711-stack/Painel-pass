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
        'apk_path',
        'output_path',
        'progress',
        'phase',
        'log',
        'patch_applied',
        'apk_filename',
        'apk_id',
    ];

    public function apk()
    {
        return $this->belongsTo(PlayFakeApp::class, 'apk_id');
    }
}