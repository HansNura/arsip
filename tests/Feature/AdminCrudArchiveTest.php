<?php

namespace Tests\Feature;

use App\Enums\BatchStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GraduationStatus;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_staf_can_edit_and_archive_program_without_batches(): void
    {
        $user = User::factory()->create();
        $program = $this->makeProgram('OLD');

        $this->actingAs($user)->put(route('admin.programs.update', $program), [
            'kode_program' => 'NEW',
            'nama' => 'Program Diperbarui',
            'deskripsi' => 'Deskripsi baru',
            'standar_kompetensi' => 'SK-02',
            'durasi_jam' => 24,
            'nilai_min_lulus' => 75,
            'is_active' => '1',
        ])->assertRedirect(route('admin.programs.index'));

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'kode_program' => 'NEW',
            'nama' => 'Program Diperbarui',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.programs.destroy', $program))
            ->assertRedirect(route('admin.programs.index'));

        $this->assertSoftDeleted('programs', ['id' => $program->id]);
    }

    public function test_program_with_batches_cannot_be_archived(): void
    {
        $user = User::factory()->create();
        $program = $this->makeProgram('PEL');
        $this->makeBatch($program, '2026-01');

        $this->actingAs($user)
            ->delete(route('admin.programs.destroy', $program))
            ->assertSessionHasErrors('program');

        $this->assertNotSoftDeleted('programs', ['id' => $program->id]);
    }

    public function test_staf_can_edit_and_archive_batch_without_enrollments(): void
    {
        $user = User::factory()->create();
        $program = $this->makeProgram('PEL');
        $batch = $this->makeBatch($program, '2026-01');

        $this->actingAs($user)->put(route('admin.batches.update', $batch), [
            'program_id' => $program->id,
            'kode_batch' => '2026-02',
            'nama' => 'Angkatan Diperbarui',
            'tahun' => 2026,
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-10',
            'kuota' => 20,
            'lokasi' => 'Ruang Baru',
            'status' => BatchStatus::Berjalan->value,
        ])->assertRedirect(route('admin.batches.index'));

        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'kode_batch' => '2026-02',
            'nama' => 'Angkatan Diperbarui',
            'status' => BatchStatus::Berjalan->value,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.batches.destroy', $batch))
            ->assertRedirect(route('admin.batches.index'));

        $this->assertSoftDeleted('batches', ['id' => $batch->id]);
    }

    public function test_batch_with_enrollments_cannot_be_archived(): void
    {
        $user = User::factory()->create();
        $program = $this->makeProgram('PEL');
        $batch = $this->makeBatch($program, '2026-01');

        $participant = Participant::create([
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Peserta Uji',
        ]);

        Enrollment::create([
            'participant_id' => $participant->id,
            'batch_id' => $batch->id,
            'status_pendaftaran' => EnrollmentStatus::Terdaftar,
            'status_kelulusan' => GraduationStatus::BelumDinilai,
            'tanggal_daftar' => '2026-01-01',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.batches.destroy', $batch))
            ->assertSessionHasErrors('batch');

        $this->assertNotSoftDeleted('batches', ['id' => $batch->id]);
    }

    private function makeProgram(string $kode): Program
    {
        return Program::create([
            'kode_program' => $kode,
            'nama' => 'Program '.$kode,
            'nilai_min_lulus' => 70,
        ]);
    }

    private function makeBatch(Program $program, string $kode): Batch
    {
        return Batch::create([
            'program_id' => $program->id,
            'kode_batch' => $kode,
            'nama' => 'Angkatan '.$kode,
            'tahun' => 2026,
            'status' => BatchStatus::Direncanakan,
        ]);
    }
}
