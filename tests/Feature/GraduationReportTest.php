<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCertificateData;
use Tests\TestCase;

class GraduationReportTest extends TestCase
{
    use BuildsCertificateData, RefreshDatabase;

    public function test_admin_can_view_graduation_summary_filtered_by_completion_period(): void
    {
        $certificate = $this->createCertificateData();
        $certificate->enrollment->batch->update(['tanggal_selesai' => '2026-02-10']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/reports/graduation?from=2026-02-01&to=2026-02-28')
            ->assertOk()
            ->assertSee('Pelatihan Kepemimpinan Dasar')
            ->assertSee('2026')
            ->assertSee('Ekspor Excel')
            ->assertSee('Ekspor PDF');
    }

    public function test_report_exports_are_xlsx_and_pdf_downloads(): void
    {
        $certificate = $this->createCertificateData();
        $certificate->enrollment->batch->update(['tanggal_selesai' => '2026-02-10']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/reports/graduation/xlsx')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition');

        $this->actingAs($admin)
            ->get('/admin/reports/graduation/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition');
    }

    public function test_report_is_restricted_to_authenticated_users(): void
    {
        $this->get('/admin/reports/graduation')->assertRedirect('/login');
    }
}
