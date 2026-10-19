<?php

namespace App\Enums;

enum CertificateStatus: string
{
    case Aktif = 'aktif';
    case Dibatalkan = 'dibatalkan';
    case Diganti = 'diganti';
}
