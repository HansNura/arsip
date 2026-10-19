<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Models\AuditLog;
use App\Models\Certificate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsCertificateData;
use Tests\TestCase;

class DatabaseConstraintTest extends TestCase
{
    use BuildsCertificateData, RefreshDatabase;

    public function test_only_one_active_certificate_is_allowed_per_enrollment(): void
    {
        $certificate = $this->createCertificateData();

        $this->expectException(QueryException::class);

        Certificate::create([
            'enrollment_id' => $certificate->enrollment_id,
            'template_id' => $certificate->template_id,
            'nomor_sertifikat' => 'PEL/2026/0002',
            'qr_token' => Str::random(48),
            'file_hash' => hash('sha256', 'second'),
            'status' => CertificateStatus::Aktif,
            'tanggal_terbit' => '2026-02-11',
        ]);
    }

    public function test_audit_logs_are_append_only(): void
    {
        $log = AuditLog::create([
            'action' => 'TEST',
            'entity_type' => 'system',
            'created_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        $log->update(['action' => 'DIUBAH']);
    }
}
