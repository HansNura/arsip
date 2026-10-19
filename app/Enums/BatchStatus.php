<?php

namespace App\Enums;

enum BatchStatus: string
{
    case Direncanakan = 'direncanakan';
    case Berjalan = 'berjalan';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';
}
