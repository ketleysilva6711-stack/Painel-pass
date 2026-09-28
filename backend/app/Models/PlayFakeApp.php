<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayFakeApp extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'developer',
        'package_name',
        'version_name',
        'version_code',
        'permissions',
        'debuggable',
        'apk_url',
        'file_path',
        'icon_path',
        'file_size',
        'slug',
        'status',
        'downloads',
    ];

    protected $casts = [
        'debuggable' => 'boolean',
    ];

    /**
     * Accessor de compatibilidade: $apk->filename
     * O model só persiste file_path; filename é derivado.
     */
    public function getFilenameAttribute(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }
        return basename($this->file_path);
    }

    public function getPermissionsArrayAttribute(): array
    {
        if (empty($this->permissions)) {
            return [];
        }
        $decoded = json_decode($this->permissions, true);
        return is_array($decoded) ? $decoded : [];
    }
}
