<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateNumberSequence extends Model
{
    protected $fillable = [
        'program_id',
        'tahun',
        'prefix',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'last_number' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
