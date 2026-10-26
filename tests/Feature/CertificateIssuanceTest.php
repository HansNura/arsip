<?php

namespace Tests\Feature;

use App\Enums\BatchStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GraduationStatus;
use App\Models\Batch;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateIssuanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-15 10:00:00');
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_staf_can_issue_a_certificate_and_pdf_is_stored(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();
        $enrollment = $this->makeEnrollment($batch, GraduationStatus::Lulus, '3201010101010001');
        $this->makeTemplate();

        $response = $this->actingAs($user)->post(route('admin.certificates.store', $enrollment));

        $response->assertRedirect()->assertSessionHas('status');

        $certificate = Certificate::firstOrFail();
        $pdf = Storage::disk('local')->get($certificate->file_path);

        $this->assertSame('PEL/2026/0001', $certificate->nomor_sertifikat);
        $this->assertSame($user->id, $certificate->diterbitkan_oleh);
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertSame(hash('sha256', $pdf), $certificate->file_hash);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ISSUE_CERTIFICATE',
            'entity_id' => $certificate->id,
        ]);
    }

    public function test_certificate_cannot_be_issued_for_participant_who_did_not_pass(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();
        $enrollment = $this->makeEnrollment($batch, GraduationStatus::TidakLulus, '3201010101010002');
        $this->makeTemplate();

        $response = $this->actingAs($user)->post(route('admin.certificates.store', $enrollment));

        $response->assertSessionHasErrors('enrollment');
        $this->assertSame(0, Certificate::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_second_active_certificate_is_rejected(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();
        $enrollment = $this->makeEnrollment($batch, GraduationStatus::Lulus, '3201010101010003');
        $this->makeTemplate();

        $this->actingAs($user)->post(route('admin.certificates.store', $enrollment))->assertSessionHas('status');

        $response = $this->actingAs($user)->post(route('admin.certificates.store', $enrollment));

        $response->assertSessionHasErrors('enrollment');
        $this->assertSame(1, Certificate::count());
    }

    public function test_batch_issue_only_processes_graduated_participants(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();
        $this->makeEnrollment($batch, GraduationStatus::Lulus, '3201010101010004');
        $this->makeEnrollment($batch, GraduationStatus::Lulus, '3201010101010005');
        $this->makeEnrollment($batch, GraduationStatus::TidakLulus, '3201010101010006');
        $this->makeTemplate();

        $response = $this->actingAs($user)->post(route('admin.certificates.store-batch-request'), [
            'batch_id' => $batch->id,
        ]);

        $response->assertRedirect();
        $this->assertSame('2 sertifikat diterbitkan, 0 dilewati, 0 gagal.', session('status'));
        $this->assertSame(2, Certificate::count());

        $this->actingAs($user)->post(route('admin.certificates.store-batch-request'), [
            'batch_id' => $batch->id,
        ])->assertRedirect();

        $this->assertSame('0 sertifikat diterbitkan, 2 dilewati, 0 gagal.', session('status'));
        $this->assertSame(2, Certificate::count());
    }

    public function test_staf_can_open_certificate_page_and_download_pdf(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();
        $enrollment = $this->makeEnrollment($batch, GraduationStatus::Lulus, '3201010101010007');
        $this->makeTemplate();

        $this->actingAs($user)->get(route('admin.certificates.index'))->assertOk();

        $this->actingAs($user)->post(route('admin.certificates.store', $enrollment));

        $certificate = Certificate::firstOrFail();
        $response = $this->actingAs($user)->get(route('admin.certificates.download', $certificate));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    public function test_download_returns_404_when_file_is_missing(): void
    {
        $user = User::factory()->create();
        $certificate = $this->makeCertificateWithMissingFile();

        $this->actingAs($user)
            ->get(route('admin.certificates.download', $certificate))
            ->assertNotFound();
    }

    public function test_guest_cannot_access_certificate_pages(): void
    {
        $this->get(route('admin.certificates.index'))->assertRedirect(route('login'));
    }

    private function makeBatch(): Batch
    {
        $program = Program::create([
            'kode_program' => 'PEL',
            'nama' => 'Pelatihan Kepemimpinan Dasar',
            'standar_kompetensi' => 'SK-01 Kepemimpinan',
            'nilai_min_lulus' => 70,
        ]);

        return Batch::create([
            'program_id' => $program->id,
            'kode_batch' => '2026-01',
            'nama' => 'Angkatan 1 Tahun 2026',
            'tahun' => 2026,
            'status' => BatchStatus::Selesai,
        ]);
    }

    private function makeEnrollment(Batch $batch, GraduationStatus $status, string $nik): Enrollment
    {
        $participant = Participant::create([
            'nik' => $nik,
            'nama_lengkap' => 'Peserta '.$nik,
        ]);

        return Enrollment::create([
            'participant_id' => $participant->id,
            'batch_id' => $batch->id,
            'status_pendaftaran' => EnrollmentStatus::Selesai,
            'status_kelulusan' => $status,
            'tanggal_daftar' => '2026-01-15',
            'nilai_akhir' => $status === GraduationStatus::Lulus ? 86.5 : 55,
        ]);
    }

    private function makeTemplate(): CertificateTemplate
    {
        return CertificateTemplate::create([
            'nama' => 'Default',
            'versi' => 1,
            'file_path' => 'templates/default.pdf',
            'is_active' => true,
        ]);
    }

    private function makeCertificateWithMissingFile(): Certificate
    {
        $batch = $this->makeBatch();
        $enrollment = $this->makeEnrollment($batch, GraduationStatus::Lulus, '3201010101010008');
        $template = $this->makeTemplate();

        return Certificate::create([
            'enrollment_id' => $enrollment->id,
            'template_id' => $template->id,
            'nomor_sertifikat' => 'PEL/2026/0009',
            'qr_token' => str_repeat('b', 48),
            'file_path' => 'certificates/2026/missing.pdf',
            'file_hash' => hash('sha256', 'missing'),
            'tanggal_terbit' => '2026-06-15',
        ]);
    }
}
