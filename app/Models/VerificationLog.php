<?php

namespace App\Models;

use App\Enums\VerificationResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nomor_sertifikat_input',
        'certificate_id',
        'hasil',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'hasil' => VerificationResult::class,
            'created_at' => 'datetime',
        ];
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }
}
