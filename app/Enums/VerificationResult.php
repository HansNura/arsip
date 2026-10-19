<?php

namespace App\Enums;

enum VerificationResult: string
{
    case Valid = 'valid';
    case Dibatalkan = 'dibatalkan';
    case TidakDitemukan = 'tidak_ditemukan';
}
