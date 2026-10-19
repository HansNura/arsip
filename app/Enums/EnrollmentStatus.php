<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Terdaftar = 'terdaftar';
    case Aktif = 'aktif';
    case MengundurkanDiri = 'mengundurkan_diri';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';
}
