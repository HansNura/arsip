<?php

return [

    'number_format' => env('CERTIFICATE_NUMBER_FORMAT', '{prefix}/{tahun}/{urut:04d}'),

    'max_attempts' => (int) env('CERTIFICATE_NUMBER_MAX_ATTEMPTS', 3),

    'disk' => env('CERTIFICATE_DISK', 'local'),

    'signature_path' => env('CERTIFICATE_SIGNATURE_PATH'),

    'penandatangan_nama' => env('CERTIFICATE_PENANDATANGAN_NAMA'),

    'penandatangan_jabatan' => env('CERTIFICATE_PENANDATANGAN_JABATAN'),

];
