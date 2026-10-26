<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use App\Models\Program;
use App\Services\CertificateNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\Concerns\BuildsCertificateData;
use Tests\TestCase;

class CertificateNumberGeneratorTest extends TestCase
{
    use BuildsCertificateData, RefreshDatabase;

    private CertificateNumberGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = app(CertificateNumberGenerator::class);
    }

    public function test_it_generates_sequential_numbers_per_program_and_year(): void
    {
        $program = $this->makeProgram('PEL');

        $this->assertSame('PEL/2026/0001', $this->generator->generate($program, 2026));
        $this->assertSame('PEL/2026/0002', $this->generator->generate($program, 2026));

        $this->assertDatabaseHas('certificate_number_sequences', [
            'program_id' => $program->id,
            'tahun' => 2026,
            'prefix' => 'PEL',
            'last_number' => 2,
        ]);
    }

    public function test_sequences_are_independent_per_program_and_year(): void
    {
        $pel = $this->makeProgram('PEL');
        $web = $this->makeProgram('WEB');

        $this->assertSame('PEL/2026/0001', $this->generator->generate($pel, 2026));
        $this->assertSame('WEB/2026/0001', $this->generator->generate($web, 2026));
        $this->assertSame('PEL/2027/0001', $this->generator->generate($pel, 2027));
        $this->assertSame('PEL/2026/0002', $this->generator->generate($pel, 2026));
    }

    public function test_prefix_is_stored_on_first_allocation(): void
    {
        $program = $this->makeProgram('PEL');

        $this->assertSame('SK-01/2026/0001', $this->generator->generate($program, 2026, 'sk-01'));
        $this->assertSame('SK-01/2026/0002', $this->generator->generate($program, 2026, 'LAIN'));
    }

    public function test_it_rejects_invalid_year(): void
    {
        $program = $this->makeProgram('PEL');

        $this->expectException(InvalidArgumentException::class);

        $this->generator->generate($program, 1899);
    }

    public function test_it_rejects_prefix_with_invalid_characters(): void
    {
        $program = $this->makeProgram('PEL');

        $this->expectException(InvalidArgumentException::class);

        $this->generator->generate($program, 2026, 'PEL/2026');
    }

    public function test_generated_number_can_be_stored_on_a_certificate(): void
    {
        $certificate = $this->createCertificateData();
        $program = $this->makeProgram('WEB');
        $nomor = $this->generator->generate($program, 2026);

        $created = Certificate::create([
            'enrollment_id' => $certificate->enrollment_id,
            'template_id' => $certificate->template_id,
            'nomor_sertifikat' => $nomor,
            'qr_token' => str_repeat('a', 48),
            'file_hash' => hash('sha256', 'generated'),
            'status' => CertificateStatus::Dibatalkan,
            'tanggal_terbit' => '2026-03-01',
        ]);

        $this->assertSame('WEB/2026/0001', $created->nomor_sertifikat);
    }

    private function makeProgram(string $kode): Program
    {
        return Program::create([
            'kode_program' => $kode,
            'nama' => 'Program '.$kode,
            'nilai_min_lulus' => 70,
        ]);
    }
}
