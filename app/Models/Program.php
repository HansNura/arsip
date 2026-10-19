<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kode_program',
        'nama',
        'deskripsi',
        'standar_kompetensi',
        'durasi_jam',
        'nilai_min_lulus',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'durasi_jam' => 'integer',
            'nilai_min_lulus' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function certificateNumberSequences(): HasMany
    {
        return $this->hasMany(CertificateNumberSequence::class);
    }
}
