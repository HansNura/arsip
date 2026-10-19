<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'email',
        'no_telepon',
        'pendidikan_terakhir',
        'pekerjaan',
        'instansi',
        'foto_path',
        'catatan',
        'archived_at',
        'storage_tier',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
