<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\RetentionPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCertificateData;
use Tests\TestCase;

class AlumniRetentionTest extends TestCase
{
    use BuildsCertificateData, RefreshDatabase;

    public function test_scheduler_places_alumni_older_than_policy_in_static_archive(): void
    {
        $certificate = $this->createCertificateData();
        $certificate->enrollment->batch->update([
            'tanggal_selesai' => now()->subYears(6)->toDateString(),
        ]);
        RetentionPolicy::query()->where('entity_type', 'participants')->update([
            'retention_years' => 5,
            'action' => 'cold_storage',
            'is_active' => true,
        ]);

        $this->artisan('retention:archive-alumni')->assertExitCode(0);

        $participant = Participant::findOrFail($certificate->enrollment->participant_id);
        $this->assertNotNull($participant->archived_at);
        $this->assertSame('cold', $participant->storage_tier);
    }

    public function test_soft_deleted_alumni_can_be_restored_by_an_admin(): void
    {
        $certificate = $this->createCertificateData();
        $certificate->enrollment->batch->update([
            'tanggal_selesai' => now()->subYears(6)->toDateString(),
        ]);
        RetentionPolicy::query()->where('entity_type', 'participants')->update([
            'retention_years' => 5,
            'action' => 'hapus_lembut',
            'is_active' => true,
        ]);

        $this->artisan('retention:archive-alumni')->assertExitCode(0);

        $participant = Participant::withTrashed()->findOrFail($certificate->enrollment->participant_id);
        $this->assertTrue($participant->trashed());

        $this->actingAs(User::factory()->admin()->create())
            ->post("/admin/retention/participants/{$participant->id}/restore")
            ->assertRedirect('/admin/retention');

        $participant->refresh();
        $this->assertFalse($participant->trashed());
        $this->assertNull($participant->archived_at);
        $this->assertSame('hot', $participant->storage_tier);
    }

    public function test_retention_settings_are_admin_only(): void
    {
        $this->get('/admin/retention')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->get('/admin/retention')
            ->assertForbidden();
    }

    public function test_default_five_year_retention_does_not_archive_at_exactly_five_years(): void
    {
        $certificate = $this->createCertificateData();
        $certificate->enrollment->batch->update([
            'tanggal_selesai' => now()->subYears(5)->toDateString(),
        ]);
        RetentionPolicy::query()->where('entity_type', 'participants')->update([
            'retention_years' => 5,
            'action' => 'cold_storage',
            'is_active' => true,
        ]);

        $this->artisan('retention:archive-alumni')->assertExitCode(0);

        $this->assertNull(Participant::findOrFail($certificate->enrollment->participant_id)->archived_at);
    }

    public function test_admin_can_create_an_active_policy_and_replaces_the_previous_active_policy(): void
    {
        $admin = User::factory()->admin()->create();
        $defaultPolicy = RetentionPolicy::query()->where('entity_type', 'participants')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/retention', [
                'nama' => 'Arsip Dinamis 7 Tahun',
                'retention_years' => 7,
                'action' => 'arsipkan',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/retention');

        $this->assertDatabaseHas('retention_policies', [
            'nama' => 'Arsip Dinamis 7 Tahun',
            'action' => 'arsipkan',
            'retention_years' => 7,
            'is_active' => 1,
            'created_by' => $admin->id,
        ]);
        $this->assertFalse($defaultPolicy->fresh()->is_active);
    }
}
