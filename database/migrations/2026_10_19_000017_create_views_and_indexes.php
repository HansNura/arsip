<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_public_certificate');

        DB::statement(<<<'SQL'
            CREATE VIEW v_public_certificate AS
            SELECT c.id AS certificate_id,
                   c.nomor_sertifikat,
                   c.status,
                   c.tanggal_terbit,
                   p.nama_lengkap,
                   pr.nama AS program,
                   b.kode_batch,
                   b.tahun
            FROM certificates c
            JOIN enrollments e ON e.id = c.enrollment_id
            JOIN participants p ON p.id = e.participant_id
            JOIN batches b ON b.id = e.batch_id
            JOIN programs pr ON pr.id = b.program_id
            WHERE p.deleted_at IS NULL
        SQL);

        DB::statement('CREATE UNIQUE INDEX ux_participants_email ON participants (lower(email)) WHERE email IS NOT NULL AND deleted_at IS NULL');
        DB::statement('CREATE INDEX ix_participants_nama ON participants (nama_lengkap COLLATE NOCASE)');
        DB::statement('CREATE UNIQUE INDEX ux_enrollments_participant_batch ON enrollments (participant_id, batch_id) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX ux_enrollments_batch_nomor ON enrollments (batch_id, nomor_peserta) WHERE nomor_peserta IS NOT NULL AND deleted_at IS NULL');
        DB::statement("CREATE UNIQUE INDEX ux_certificates_active_enrollment ON certificates (enrollment_id) WHERE status = 'aktif'");

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_audit_logs_no_update
            BEFORE UPDATE ON audit_logs
            BEGIN
                SELECT RAISE(ABORT, 'audit_logs bersifat append-only');
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_audit_logs_no_delete
            BEFORE DELETE ON audit_logs
            BEGIN
                SELECT RAISE(ABORT, 'audit_logs bersifat append-only');
            END
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_audit_logs_no_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_audit_logs_no_update');
        DB::statement('DROP INDEX IF EXISTS ux_certificates_active_enrollment');
        DB::statement('DROP INDEX IF EXISTS ux_enrollments_batch_nomor');
        DB::statement('DROP INDEX IF EXISTS ux_enrollments_participant_batch');
        DB::statement('DROP INDEX IF EXISTS ix_participants_nama');
        DB::statement('DROP INDEX IF EXISTS ux_participants_email');
        DB::statement('DROP VIEW IF EXISTS v_public_certificate');
    }
};
