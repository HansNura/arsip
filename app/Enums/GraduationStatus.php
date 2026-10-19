<?php

namespace App\Enums;

enum GraduationStatus: string
{
    case BelumDinilai = 'belum_dinilai';
    case Lulus = 'lulus';
    case TidakLulus = 'tidak_lulus';
}
