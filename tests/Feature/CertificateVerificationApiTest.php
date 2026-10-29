<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCertificateData;
use Tests\TestCase;

class CertificateVerificationApiTest extends TestCase
{
    use BuildsCertificateData, RefreshDatabase;

    public function test_api_returns_valid_certificate_without_sensitive_data(): void
    {
        $certificate = $this->createCertificateData();

        $response = $this->getJson('/api/verify?nomor='.urlencode($certificate->nomor_sertifikat));

        $response->assertOk()
            ->assertJsonPath('hasil', 'valid')
            ->assertJsonPath('sertifikat.nomor_sertifikat', $certificate->nomor_sertifikat)
            ->assertJsonPath('sertifikat.status', 'aktif')
            ->assertJsonPath('sertifikat.nama', 'Budi Santoso')
            ->assertJsonPath('sertifikat.program', 'Pelatihan Kepemimpinan Dasar')
            ->assertJsonPath('sertifikat.kode_batch', '2026-01')
            ->assertJsonPath('sertifikat.tahun', 2026)
            ->assertJsonPath('sertifikat.tanggal_terbit', '2026-02-10')
            ->assertJsonStructure([
                'hasil',
                'sertifikat' => [
                    'nomor_sertifikat',
                    'status',
                    'nama',
                    'program',
                    'kode_batch',
                    'tahun',
                    'tanggal_terbit',
                ],
            ]);

        $this->assertStringNotContainsString('3201010101010001', $response->getContent());
        $this->assertStringNotContainsString('Jl. Rahasia No. 1', $response->getContent());
        $this->assertStringNotContainsString('budi@example.test', $response->getContent());

        $this->assertDatabaseHas('verification_logs', [
            'nomor_sertifikat_input' => $certificate->nomor_sertifikat,
            'certificate_id' => $certificate->id,
            'hasil' => 'valid',
        ]);
    }

    public function test_api_marks_cancelled_certificate(): void
    {
        $certificate = $this->createCertificateData();

        $certificate->update([
            'status' => CertificateStatus::Dibatalkan,
            'dibatalkan_at' => now(),
        ]);

        $this->getJson('/api/verify?nomor='.urlencode($certificate->nomor_sertifikat))
            ->assertOk()
            ->assertJsonPath('hasil', 'dibatalkan')
            ->assertJsonPath('sertifikat.status', 'dibatalkan');
    }

    public function test_api_returns_not_found_for_unknown_number(): void
    {
        $this->getJson('/api/verify?nomor=TIDAK-ADA')
            ->assertOk()
            ->assertJsonPath('hasil', 'tidak_ditemukan')
            ->assertJsonPath('sertifikat', null);

        $this->assertDatabaseHas('verification_logs', [
            'nomor_sertifikat_input' => 'TIDAK-ADA',
            'certificate_id' => null,
            'hasil' => 'tidak_ditemukan',
        ]);
    }

    public function test_api_requires_a_certificate_number(): void
    {
        $this->getJson('/api/verify')
            ->assertStatus(422)
            ->assertJsonValidationErrors('nomor');
    }

    public function test_api_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->getJson('/api/verify?nomor=TIDAK-ADA')->assertOk();
        }

        $this->getJson('/api/verify?nomor=TIDAK-ADA')->assertStatus(429);
    }
}
