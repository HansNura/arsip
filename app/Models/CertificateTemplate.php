<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    protected $fillable = [
        'nama',
        'versi',
        'file_path',
        'tanda_tangan_path',
        'nama_penandatangan',
        'jabatan_penandatangan',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'versi' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'template_id');
    }
}
