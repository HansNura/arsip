<?php

namespace App\Models;

use App\Enums\ReissueStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateReissue extends Model
{
    protected $fillable = [
        'original_certificate_id',
        'new_certificate_id',
        'alasan',
        'pemohon_nama',
        'pemohon_kontak',
        'status',
        'diproses_oleh',
        'disetujui_oleh',
        'decided_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReissueStatus::class,
            'decided_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function originalCertificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'original_certificate_id');
    }

    public function newCertificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'new_certificate_id');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
