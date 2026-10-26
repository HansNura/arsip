<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

final class CertificateQrCode
{
    public function dataUri(string $url): string
    {
        $result = (new Builder(
            writer: new PngWriter,
            data: $url,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 320,
            margin: 2,
        ))->build();

        return $result->getDataUri();
    }
}
