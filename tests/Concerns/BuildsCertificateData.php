<?php

namespace Tests\Concerns;

use App\Enums\BatchStatus;
use App\Enums\CertificateStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GraduationStatus;
use App\Models\Batch;
use App\Models\Certificate;
use App\Models\CertificateNumberSequence;
use App\Models\CertificateTemplate;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\Program;
use Illuminate\Support\Str;

trait BuildsCertificateData
{
    /**
     * @param  array<string, mixed>  $certificateAttributes
     */
    protected function createCertificateData(array $certificateAttributes = []): Certificate
    {
        $program = Program::create([
            'kode_program' => 'PEL',
            'nama' => 'Pelatihan Kepemimpinan Dasar',
            'nilai_min_lulus' => 70,
        ]);

        $batch = Batch::create([
            'program_id' => $program->id,
            'kode_batch' => '2026-01',
            'nama' => 'Angkatan 1 Tahun 2026',
            'tahun' => 2026,
            'status' => BatchStatus::Selesai,
        ]);

        $participant = Participant::create([
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Budi Santoso',
            'alamat' => 'Jl. Rahasia No. 1',
            'email' => 'budi@example.test',
        ]);

        $enrollment = Enrollment::create([
            'participant_id' => $participant->id,
            'batch_id' => $batch->id,
            'status_pendaftaran' => EnrollmentStatus::Selesai,
            'status_kelulusan' => GraduationStatus::Lulus,
            'tanggal_daftar' => '2026-01-15',
            'nilai_akhir' => 86.50,
        ]);

        $template = CertificateTemplate::create([
            'nama' => 'Default',
            'versi' => 1,
            'file_path' => 'templates/default.pdf',
            'is_active' => true,
        ]);

        CertificateNumberSequence::create([
            'program_id' => $program->id,
            'tahun' => 2026,
            'prefix' => 'PEL',
            'last_number' => 1,
        ]);

        return Certificate::create(array_merge([
            'enrollment_id' => $enrollment->id,
            'template_id' => $template->id,
            'nomor_sertifikat' => 'PEL/2026/0001',
            'qr_token' => Str::random(48),
            'file_path' => 'certificates/demo.pdf',
            'file_hash' => hash('sha256', 'demo'),
            'status' => CertificateStatus::Aktif,
            'tanggal_terbit' => '2026-02-10',
        ], $certificateAttributes));
    }
}
