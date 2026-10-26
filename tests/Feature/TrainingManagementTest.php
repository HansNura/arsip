<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\BatchStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GraduationStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\Program;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staf_can_create_program_and_batch(): void
    {
        $staf = User::factory()->create(['role' => UserRole::StafArsip]);

        $this->actingAs($staf)
            ->post(route('admin.programs.store'), [
                'kode_program' => 'WEB',
                'nama' => 'Pelatihan Web',
                'durasi_jam' => 20,
                'nilai_min_lulus' => 75,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.programs.index'));

        $program = Program::where('kode_program', 'WEB')->firstOrFail();

        $this->post(route('admin.batches.store'), [
            'program_id' => $program->id,
            'kode_batch' => 'WEB-2026-01',
            'nama' => 'Kelas Web 1',
            'tahun' => 2026,
            'status' => BatchStatus::Direncanakan->value,
        ])->assertRedirect(route('admin.batches.index'));

        $this->assertDatabaseHas('batches', ['kode_batch' => 'WEB-2026-01']);

        $batch = Batch::where('kode_batch', 'WEB-2026-01')->firstOrFail();
        $this->put(route('admin.programs.update', $program), [
            'kode_program' => 'WEB',
            'nama' => 'Pelatihan Web Lanjutan',
            'nilai_min_lulus' => 80,
            'is_active' => true,
        ])->assertRedirect(route('admin.programs.index'));
        $this->put(route('admin.batches.update', $batch), [
            'program_id' => $program->id,
            'kode_batch' => 'WEB-2026-01',
            'nama' => 'Kelas Web 1 Revisi',
            'tahun' => 2026,
            'status' => BatchStatus::Berjalan->value,
        ])->assertRedirect(route('admin.batches.index'));
        $this->assertDatabaseHas('batches', ['id' => $batch->id, 'nama' => 'Kelas Web 1 Revisi']);

        $this->delete(route('admin.batches.destroy', $batch))->assertRedirect(route('admin.batches.index'));
        $this->assertSoftDeleted('batches', ['id' => $batch->id]);
        $this->delete(route('admin.programs.destroy', $program))->assertRedirect(route('admin.programs.index'));
        $this->assertSoftDeleted('programs', ['id' => $program->id]);
    }

    public function test_staff_can_register_participants_record_attendance_and_score_per_batch(): void
    {
        $staf = User::factory()->create(['role' => UserRole::StafArsip]);
        $program = Program::create([
            'kode_program' => 'PEL',
            'nama' => 'Pelatihan',
            'nilai_min_lulus' => 70,
        ]);
        $batch = Batch::create([
            'program_id' => $program->id,
            'kode_batch' => 'PEL-2026',
            'nama' => 'Kelas A',
            'tahun' => 2026,
            'status' => BatchStatus::Berjalan,
        ]);

        $this->actingAs($staf)
            ->post(route('admin.batches.enrollments.store', $batch), [
                'nik' => '3201010101010001',
                'nama_lengkap' => 'Peserta Contoh',
                'tanggal_daftar' => '2026-01-10',
                'status_pendaftaran' => EnrollmentStatus::Aktif->value,
            ])
            ->assertRedirect();

        $enrollment = Enrollment::firstOrFail();
        $this->get(route('admin.batches.show', $batch))
            ->assertOk()
            ->assertSee('Daftarkan peserta')
            ->assertSee('Peserta Contoh');

        $this->post(route('admin.batches.sessions.store', $batch), [
            'pertemuan_ke' => 1,
            'tanggal' => '2026-02-01',
            'topik' => 'Pembukaan',
        ])->assertRedirect();
        $session = TrainingSession::firstOrFail();
        $this->get(route('admin.batches.show', $batch))
            ->assertOk()
            ->assertSee('Pertemuan 1');

        $this->put(route('admin.batches.attendance.update', [$batch, $session]), [
            'attendance' => [$enrollment->id => AttendanceStatus::Hadir->value],
        ])->assertRedirect();

        $this->put(route('admin.batches.scores.update', [$batch, $enrollment]), [
            'nilai_akhir' => 85,
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance', [
            'enrollment_id' => $enrollment->id,
            'training_session_id' => $session->id,
            'status' => AttendanceStatus::Hadir->value,
        ]);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'nilai_akhir' => 85,
            'status_kelulusan' => GraduationStatus::Lulus->value,
        ]);
    }

    public function test_participant_search_combines_nik_name_year_and_batch_filters(): void
    {
        $staff = User::factory()->create(['role' => UserRole::StafArsip]);
        $program = Program::create(['kode_program' => 'PEL', 'nama' => 'Pelatihan', 'nilai_min_lulus' => 70]);
        $batch = Batch::create([
            'program_id' => $program->id,
            'kode_batch' => 'PEL-2026',
            'nama' => 'Kelas A',
            'tahun' => 2026,
            'status' => BatchStatus::Direncanakan,
        ]);
        $participant = Participant::create(['nik' => '3201010101010001', 'nama_lengkap' => 'Budi Santoso']);
        $batch->enrollments()->create([
            'participant_id' => $participant->id,
            'tanggal_daftar' => '2026-01-01',
        ]);

        $this->actingAs($staff)
            ->get(route('admin.participants.index', [
                'nik' => '0001',
                'nama' => 'Budi',
                'tahun' => 2026,
                'batch_id' => $batch->id,
            ]))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('3201010101010001');

        $this->get(route('admin.participants.index', ['nama' => 'Nama Tidak Ada']))
            ->assertOk()
            ->assertSee('Tidak ada peserta yang cocok dengan filter.');
    }

    public function test_registration_rejects_duplicate_and_full_batch(): void
    {
        $staff = User::factory()->create(['role' => UserRole::StafArsip]);
        $program = Program::create(['kode_program' => 'PEL', 'nama' => 'Pelatihan', 'nilai_min_lulus' => 70]);
        $batch = Batch::create([
            'program_id' => $program->id,
            'kode_batch' => 'PEL-2026',
            'nama' => 'Kelas A',
            'tahun' => 2026,
            'kuota' => 1,
            'status' => BatchStatus::Direncanakan,
        ]);
        $participant = Participant::create(['nik' => '3201010101010001', 'nama_lengkap' => 'Peserta Contoh']);
        $batch->enrollments()->create([
            'participant_id' => $participant->id,
            'tanggal_daftar' => '2026-01-01',
        ]);

        $this->actingAs($staff)
            ->from(route('admin.batches.show', $batch))
            ->post(route('admin.batches.enrollments.store', $batch), [
                'nik' => $participant->nik,
                'nama_lengkap' => $participant->nama_lengkap,
                'tanggal_daftar' => '2026-01-02',
                'status_pendaftaran' => EnrollmentStatus::Terdaftar->value,
            ])
            ->assertSessionHasErrors('nik');

        $this->assertSame(1, Enrollment::count());
    }
}
