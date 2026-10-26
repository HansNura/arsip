<?php

return [

    'number_format' => env('CERTIFICATE_NUMBER_FORMAT', '{prefix}/{tahun}/{urut:04d}'),

    'max_attempts' => (int) env('CERTIFICATE_NUMBER_MAX_ATTEMPTS', 3),

];
