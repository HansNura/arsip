<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\GraduationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'participant_id',
        'batch_id',
        'nomor_peserta',
        'status_pendaftaran',
        'status_kelulusan',
        'tanggal_daftar',
        'nilai_akhir',
    ];

    protected function casts(): array
    {
        return [
            'status_pendaftaran' => EnrollmentStatus::class,
            'status_kelulusan' => GraduationStatus::class,
            'tanggal_daftar' => 'date',
            'nilai_akhir' => 'decimal:2',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
