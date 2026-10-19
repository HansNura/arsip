<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Certificate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'template_id',
        'nomor_sertifikat',
        'qr_token',
        'file_path',
        'file_hash',
        'status',
        'tanggal_terbit',
        'diterbitkan_oleh',
        'dibatalkan_at',
        'alasan_pembatalan',
        'replaced_by_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => CertificateStatus::class,
            'tanggal_terbit' => 'date',
            'dibatalkan_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function diterbitkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_id');
    }

    public function reissues(): HasMany
    {
        return $this->hasMany(CertificateReissue::class, 'original_certificate_id');
    }

    public function replacementReissue(): HasOne
    {
        return $this->hasOne(CertificateReissue::class, 'new_certificate_id');
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(VerificationLog::class);
    }
}
