<?php

namespace Tests\Feature;

use App\Enums\BatchStatus;
use App\Enums\CertificateStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GraduationStatus;
use App\Models\Batch;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use App\Services\CertificateIssuer;
use App\Services\CertificateQrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateQrAndSignatureTest extends TestCase
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

    public function test_qr_token_verifies_certificate_without_exposing_sensitive_data(): void
    {
        $certificate = $this->issueCertificate();

        $this->get(route('verify.token', ['token' => $certificate->qr_token]))
            ->assertOk()
            ->assertSee('Sertifikat valid')
            ->assertSee($certificate->nomor_sertifikat)
            ->assertSee('Budi Santoso')
            ->assertSee('Pelatihan Kepemimpinan Dasar')
            ->assertDontSee('3201010101010001')
            ->assertDontSee('Jl. Rahasia No. 1')
            ->assertDontSee('budi@example.test')
            ->assertDontSee('081200000001');

        $this->assertDatabaseHas('verification_logs', [
            'nomor_sertifikat_input' => $certificate->nomor_sertifikat,
            'certificate_id' => $certificate->id,
            'hasil' => 'valid',
        ]);
    }

    public function test_unknown_qr_token_is_logged_as_not_found(): void
    {
        $token = str_repeat('z', 48);

        $this->get(route('verify.token', ['token' => $token]))
            ->assertOk()
            ->assertSee('Tautan verifikasi tidak dikenali')
            ->assertDontSee($token);

        $this->assertDatabaseHas('verification_logs', [
            'nomor_sertifikat_input' => $token,
            'certificate_id' => null,
            'hasil' => 'tidak_ditemukan',
        ]);
    }

    public function test_cancelled_certificate_via_qr_is_marked_invalid(): void
    {
        $certificate = $this->issueCertificate();

        $certificate->update([
            'status' => CertificateStatus::Dibatalkan,
            'dibatalkan_at' => now(),
        ]);

        $this->get(route('verify.token', ['token' => $certificate->qr_token]))
            ->assertOk()
            ->assertSee('Sertifikat tidak berlaku');
    }

    public function test_qr_code_service_returns_png_data_uri(): void
    {
        $dataUri = app(CertificateQrCode::class)->dataUri('http://localhost/verify/s/contoh');

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);

        $binary = base64_decode(substr($dataUri, strlen('data:image/png;base64,')), true);

        $this->assertIsString($binary);
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $binary);
    }

    public function test_admin_can_upload_signature_and_signer_details(): void
    {
        $user = User::factory()->admin()->create();
        $template = $this->makeTemplate();

        $response = $this->actingAs($user)->put(route('admin.certificate-templates.update'), [
            'nama_penandatangan' => 'Dr. Contoh Penandatangan',
            'jabatan_penandatangan' => 'Kepala Lembaga',
            'tanda_tangan' => UploadedFile::fake()->image('ttd.png', 240, 80),
        ]);

        $response->assertRedirect()->assertSessionHas('status');

        $template->refresh();

        $this->assertSame('Dr. Contoh Penandatangan', $template->nama_penandatangan);
        $this->assertSame('Kepala Lembaga', $template->jabatan_penandatangan);
        $this->assertNotNull($template->tanda_tangan_path);
        Storage::disk('local')->assertExists($template->tanda_tangan_path);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'UPDATE_CERTIFICATE_TEMPLATE',
            'entity_id' => $template->id,
        ]);
    }

    public function test_replacing_signature_deletes_the_old_file(): void
    {
        $user = User::factory()->admin()->create();
        $template = $this->makeTemplate();

        $this->actingAs($user)->put(route('admin.certificate-templates.update'), [
            'tanda_tangan' => UploadedFile::fake()->image('pertama.png', 200, 60),
        ]);

        $oldPath = $template->fresh()->tanda_tangan_path;
        Storage::disk('local')->assertExists($oldPath);

        $this->actingAs($user)->put(route('admin.certificate-templates.update'), [
            'tanda_tangan' => UploadedFile::fake()->image('kedua.png', 240, 80),
        ]);

        $newPath = $template->fresh()->tanda_tangan_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);
    }

    public function test_invalid_signature_upload_is_rejected(): void
    {
        $user = User::factory()->admin()->create();
        $template = $this->makeTemplate();

        $response = $this->actingAs($user)->put(route('admin.certificate-templates.update'), [
            'tanda_tangan' => UploadedFile::fake()->create('ttd.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('tanda_tangan');
        $this->assertNull($template->fresh()->tanda_tangan_path);
    }

    public function test_staf_can_open_certificate_template_page(): void
    {
        $user = User::factory()->create();
        $this->makeTemplate();

        $this->actingAs($user)
            ->get(route('admin.certificate-templates.edit'))
            ->assertOk()
            ->assertSee('Template Sertifikat');
    }

    public function test_guest_cannot_edit_certificate_template(): void
    {
        $this->get(route('admin.certificate-templates.edit'))->assertRedirect(route('login'));
    }

    private function issueCertificate(): Certificate
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();
        $enrollment = $this->makeEnrollment($batch);
        $this->makeTemplate();

        return app(CertificateIssuer::class)->issue($enrollment, null, $user);
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

    private function makeEnrollment(Batch $batch): Enrollment
    {
        $participant = Participant::create([
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Budi Santoso',
            'alamat' => 'Jl. Rahasia No. 1',
            'email' => 'budi@example.test',
            'no_telepon' => '081200000001',
        ]);

        return Enrollment::create([
            'participant_id' => $participant->id,
            'batch_id' => $batch->id,
            'status_pendaftaran' => EnrollmentStatus::Selesai,
            'status_kelulusan' => GraduationStatus::Lulus,
            'tanggal_daftar' => '2026-01-15',
            'nilai_akhir' => 86.5,
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
}
