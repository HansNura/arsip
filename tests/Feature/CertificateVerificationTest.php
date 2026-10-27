<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCertificateData;
use Tests\TestCase;

class CertificateVerificationTest extends TestCase
{
    use BuildsCertificateData, RefreshDatabase;

    public function test_valid_certificate_can_be_verified_without_exposing_sensitive_data(): void
    {
        $certificate = $this->createCertificateData();

        $this->get('/verify?nomor='.urlencode($certificate->nomor_sertifikat))
            ->assertOk()
            ->assertSee('Status validitas')
            ->assertSee('Valid')
            ->assertSee($certificate->nomor_sertifikat)
            ->assertSee('Budi Santoso')
            ->assertSee('Pelatihan Kepemimpinan Dasar')
            ->assertDontSee('3201010101010001')
            ->assertDontSee('Jl. Rahasia No. 1')
            ->assertDontSee('budi@example.test');

        $this->assertDatabaseHas('verification_logs', [
            'nomor_sertifikat_input' => $certificate->nomor_sertifikat,
            'hasil' => 'valid',
            'certificate_id' => $certificate->id,
        ]);
    }

    public function test_cancelled_certificate_is_marked_invalid(): void
    {
        $certificate = $this->createCertificateData();

        $certificate->update([
            'status' => CertificateStatus::Dibatalkan,
            'dibatalkan_at' => now(),
        ]);

        $this->get('/verify?nomor='.urlencode($certificate->nomor_sertifikat))
            ->assertOk()
            ->assertSee('Dibatalkan');
    }

    public function test_unknown_certificate_number_is_not_found(): void
    {
        $this->get('/verify?nomor=TIDAK-ADA')
            ->assertOk()
            ->assertSee('Tidak Ditemukan');

        $this->assertDatabaseHas('verification_logs', [
            'nomor_sertifikat_input' => 'TIDAK-ADA',
            'hasil' => 'tidak_ditemukan',
            'certificate_id' => null,
        ]);
    }

    public function test_form_submission_redirects_to_result(): void
    {
        $this->followingRedirects()
            ->post('/verify', ['nomor_sertifikat' => 'PEL/2026/9999'])
            ->assertOk()
            ->assertSee('Tidak Ditemukan')
            ->assertSee('PEL/2026/9999');
    }
}
