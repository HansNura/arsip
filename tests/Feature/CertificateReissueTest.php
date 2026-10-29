<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Enums\ReissueStatus;
use App\Models\Certificate;
use App\Models\CertificateReissue;
use App\Models\User;
use App\Services\CertificateReissueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsCertificateData;
use Tests\TestCase;

class CertificateReissueTest extends TestCase
{
    use BuildsCertificateData, RefreshDatabase;

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

    public function test_staf_can_create_reissue_request(): void
    {
        $staff = User::factory()->create();
        $certificate = $this->createCertificateData();

        $response = $this->actingAs($staff)->post(route('admin.reissues.store'), [
            'nomor_sertifikat' => $certificate->nomor_sertifikat,
            'alasan' => 'Sertifikat hilang saat pindah kantor.',
            'pemohon_nama' => 'Budi Santoso',
            'pemohon_kontak' => '081200000001',
        ]);

        $reissue = CertificateReissue::firstOrFail();

        $response->assertRedirect(route('admin.reissues.show', $reissue));
        $this->assertSame($certificate->id, $reissue->original_certificate_id);
        $this->assertSame(ReissueStatus::Diajukan, $reissue->status);
        $this->assertSame($staff->id, $reissue->diproses_oleh);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE_REISSUE',
            'entity_id' => $reissue->id,
        ]);
    }

    public function test_reissue_requires_an_active_certificate(): void
    {
        $staff = User::factory()->create();
        $certificate = $this->createCertificateData();
        $certificate->update([
            'status' => CertificateStatus::Dibatalkan,
            'dibatalkan_at' => now(),
        ]);

        $this->actingAs($staff)->post(route('admin.reissues.store'), [
            'nomor_sertifikat' => $certificate->nomor_sertifikat,
            'alasan' => 'Sertifikat rusak.',
        ])->assertSessionHasErrors('nomor_sertifikat');

        $this->assertSame(0, CertificateReissue::count());
    }

    public function test_duplicate_open_reissue_is_rejected(): void
    {
        $staff = User::factory()->create();
        $reissue = $this->createReissue($staff);

        $this->actingAs($staff)->post(route('admin.reissues.store'), [
            'nomor_sertifikat' => $reissue->originalCertificate->nomor_sertifikat,
            'alasan' => 'Pengajuan kedua.',
        ])->assertSessionHasErrors('nomor_sertifikat');

        $this->assertSame(1, CertificateReissue::count());
    }

    public function test_admin_can_approve_reissue(): void
    {
        $staff = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $reissue = $this->createReissue($staff);

        $this->actingAs($admin)
            ->post(route('admin.reissues.approve', $reissue), [
                'catatan_keputusan' => 'Dokumen pendukung lengkap.',
            ])
            ->assertRedirect();

        $reissue->refresh();
        $this->assertSame(ReissueStatus::Disetujui, $reissue->status);
        $this->assertSame($admin->id, $reissue->disetujui_oleh);
        $this->assertNotNull($reissue->decided_at);
        $this->assertSame('Dokumen pendukung lengkap.', $reissue->catatan_keputusan);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'APPROVE_REISSUE',
            'entity_id' => $reissue->id,
        ]);
    }

    public function test_staf_cannot_approve_reissue(): void
    {
        $staff = User::factory()->create();
        $reissue = $this->createReissue($staff);

        $this->actingAs($staff)
            ->post(route('admin.reissues.approve', $reissue))
            ->assertForbidden();

        $this->assertSame(ReissueStatus::Diajukan, $reissue->fresh()->status);
    }

    public function test_approver_must_differ_from_the_creator(): void
    {
        $admin = User::factory()->admin()->create();
        $reissue = $this->createReissue($admin);

        $this->actingAs($admin)
            ->post(route('admin.reissues.approve', $reissue))
            ->assertSessionHasErrors('reissue');

        $this->assertSame(ReissueStatus::Diajukan, $reissue->fresh()->status);
    }

    public function test_admin_can_reject_reissue_with_a_note(): void
    {
        $staff = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $reissue = $this->createReissue($staff);

        $this->actingAs($admin)
            ->post(route('admin.reissues.reject', $reissue), [
                'catatan_keputusan' => 'Dokumen pendukung tidak sesuai.',
            ])
            ->assertRedirect();

        $reissue->refresh();
        $this->assertSame(ReissueStatus::Ditolak, $reissue->status);
        $this->assertSame('Dokumen pendukung tidak sesuai.', $reissue->catatan_keputusan);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'REJECT_REISSUE',
            'entity_id' => $reissue->id,
        ]);
    }

    public function test_staf_can_process_approved_reissue(): void
    {
        $staff = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $reissue = $this->createReissue($staff);
        $original = $reissue->originalCertificate;

        $this->actingAs($admin)->post(route('admin.reissues.approve', $reissue));

        $this->actingAs($staff)
            ->post(route('admin.reissues.process', $reissue))
            ->assertRedirect(route('admin.reissues.show', $reissue));

        $reissue->refresh();
        $original->refresh();
        $replacement = $reissue->newCertificate;

        $this->assertSame(ReissueStatus::Selesai, $reissue->status);
        $this->assertNotNull($reissue->completed_at);
        $this->assertNotNull($replacement);
        $this->assertSame('PEL/2026/0002', $replacement->nomor_sertifikat);
        $this->assertSame(CertificateStatus::Diganti, $original->status);
        $this->assertSame($replacement->id, $original->replaced_by_id);
        $this->assertSame(CertificateStatus::Aktif, $replacement->status);
        $this->assertSame($original->enrollment_id, $replacement->enrollment_id);

        Storage::disk('local')->assertExists($replacement->file_path);
        $this->assertSame(hash('sha256', Storage::disk('local')->get($replacement->file_path)), $replacement->file_hash);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'REISSUE',
            'entity_id' => $reissue->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'REPLACE_CERTIFICATE',
            'entity_id' => $original->id,
        ]);
    }

    public function test_unapproved_reissue_cannot_be_processed(): void
    {
        $staff = User::factory()->create();
        $reissue = $this->createReissue($staff);

        $this->actingAs($staff)
            ->post(route('admin.reissues.process', $reissue))
            ->assertSessionHasErrors('reissue');

        $this->assertSame(ReissueStatus::Diajukan, $reissue->fresh()->status);
        $this->assertSame(CertificateStatus::Aktif, $reissue->originalCertificate->fresh()->status);
        $this->assertSame(1, Certificate::count());
    }

    public function test_staf_can_view_reissue_pages(): void
    {
        $staff = User::factory()->create();
        $reissue = $this->createReissue($staff);

        $this->actingAs($staff)->get(route('admin.reissues.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.reissues.show', $reissue))->assertOk();
    }

    public function test_guest_cannot_access_reissue_pages(): void
    {
        $this->get(route('admin.reissues.index'))->assertRedirect(route('login'));
    }

    private function createReissue(User $actor): CertificateReissue
    {
        $certificate = $this->createCertificateData();

        return app(CertificateReissueService::class)->create($certificate, [
            'alasan' => 'Sertifikat hilang.',
            'pemohon_nama' => 'Budi Santoso',
            'pemohon_kontak' => '081200000001',
        ], $actor);
    }
}
