<?php

namespace Database\Seeders;

use App\Enums\BatchStatus;
use App\Enums\CertificateStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GraduationStatus;
use App\Models\Assessment;
use App\Models\Batch;
use App\Models\Certificate;
use App\Models\CertificateNumberSequence;
use App\Models\CertificateTemplate;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Participant;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $program = Program::updateOrCreate(
            ['kode_program' => 'PEL'],
            [
                'nama' => 'Pelatihan Kepemimpinan Dasar',
                'deskripsi' => 'Program contoh untuk demonstrasi lokal.',
                'standar_kompetensi' => 'SK-01 Kepemimpinan',
                'durasi_jam' => 40,
                'nilai_min_lulus' => 70,
                'is_active' => true,
            ],
        );

        $batch = Batch::updateOrCreate(
            ['kode_batch' => '2026-01'],
            [
                'program_id' => $program->id,
                'nama' => 'Angkatan 1 Tahun 2026',
                'tahun' => 2026,
                'tanggal_mulai' => '2026-02-02',
                'tanggal_selesai' => '2026-02-06',
                'kuota' => 30,
                'lokasi' => 'Ruang Pelatihan Utama',
                'status' => BatchStatus::Selesai->value,
            ],
        );

        $participant = Participant::updateOrCreate(
            ['nik' => '3201010101010001'],
            [
                'nama_lengkap' => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1994-05-17',
                'alamat' => 'Jl. Contoh No. 1, Bandung',
                'email' => 'budi.santoso@example.test',
                'no_telepon' => '081200000001',
                'pendidikan_terakhir' => 'S1',
                'pekerjaan' => 'Staf',
                'instansi' => 'PT Contoh Nusantara',
            ],
        );

        $enrollment = Enrollment::updateOrCreate(
            [
                'participant_id' => $participant->id,
                'batch_id' => $batch->id,
            ],
            [
                'nomor_peserta' => '001',
                'status_pendaftaran' => EnrollmentStatus::Selesai->value,
                'status_kelulusan' => GraduationStatus::Lulus->value,
                'tanggal_daftar' => '2026-01-15',
                'nilai_akhir' => 86.50,
            ],
        );

        $teori = Assessment::updateOrCreate(
            ['program_id' => $program->id, 'nama' => 'Teori'],
            ['bobot' => 40, 'urutan' => 1],
        );

        $praktik = Assessment::updateOrCreate(
            ['program_id' => $program->id, 'nama' => 'Praktik'],
            ['bobot' => 60, 'urutan' => 2],
        );

        Grade::updateOrCreate(
            ['enrollment_id' => $enrollment->id, 'assessment_id' => $teori->id],
            ['nilai' => 85, 'dinilai_at' => now()],
        );

        Grade::updateOrCreate(
            ['enrollment_id' => $enrollment->id, 'assessment_id' => $praktik->id],
            ['nilai' => 87.50, 'dinilai_at' => now()],
        );

        $template = CertificateTemplate::updateOrCreate(
            ['nama' => 'Default', 'versi' => 1],
            ['file_path' => 'templates/default.pdf', 'is_active' => true],
        );

        CertificateNumberSequence::updateOrCreate(
            ['program_id' => $program->id, 'tahun' => 2026],
            ['prefix' => 'PEL', 'last_number' => 1],
        );

        Certificate::updateOrCreate(
            ['nomor_sertifikat' => 'PEL/2026/0001'],
            [
                'enrollment_id' => $enrollment->id,
                'template_id' => $template->id,
                'qr_token' => Str::random(48),
                'file_path' => 'certificates/PEL-2026-0001.pdf',
                'file_hash' => hash('sha256', 'demo-certificate'),
                'status' => CertificateStatus::Aktif->value,
                'tanggal_terbit' => '2026-02-10',
            ],
        );
    }
}
