<?php

namespace App\Services;

use App\Enums\VerificationResult;
use App\Models\VerificationLog;
use Illuminate\Support\Str;

final class VerificationLogger
{
    public function log(string $input, ?int $certificateId, VerificationResult $result): void
    {
        VerificationLog::create([
            'nomor_sertifikat_input' => Str::limit($input, 120, ''),
            'certificate_id' => $certificateId,
            'hasil' => $result,
            'ip_address' => request()->ip() ?? 'unknown',
            'user_agent' => Str::limit((string) request()->userAgent(), 1000, ''),
            'created_at' => now(),
        ]);
    }
}
