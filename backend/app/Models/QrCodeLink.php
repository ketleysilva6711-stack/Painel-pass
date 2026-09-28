<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCodeLink extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'target_url',
        'qr_path',
        'scans',
    ];

    protected $appends = ['qr_url'];

    public function getQrUrlAttribute(): ?string
    {
        if (!empty($this->qr_path)) {
            return url('storage/' . $this->qr_path);
        }
        if (!empty($this->id)) {
            return url('/api/q/' . $this->id . '.png');
        }
        return null;
    }
}
