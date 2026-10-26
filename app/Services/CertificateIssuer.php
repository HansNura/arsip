<?php

namespace App\Services;

use App\Enums\CertificateStatus;
use App\Enums\GraduationStatus;
use App\Models\Batch;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class CertificateIssuer
{
    public function __construct(
        private CertificateNumberGenerator $numbers,
        private CertificatePdfRenderer $pdf,
        private CertificateQrCode $qr,
        private CertificateSignature $signature,
    ) {}

    public function issue(
        Enrollment $enrollment,
        ?CertificateTemplate $template = null,
        ?User $actor = null,
    ): Certificate {
        $enrollment->loadMissing(['participant', 'batch.program']);
        $template ??= $this->defaultTemplate();

        if ($enrollment->status_kelulusan !== GraduationStatus::Lulus) {
            throw ValidationException::withMessages([
                'enrollment' => 'Sertifikat hanya dapat diterbitkan untuk peserta yang lulus.',
            ]);
        }

        if ($enrollment->certificates()->where('status', CertificateStatus::Aktif->value)->exists()) {
            throw ValidationException::withMessages([
                'enrollment' => 'Peserta sudah memiliki sertifikat aktif.',
            ]);
        }

        $tahun = (int) now()->year;
        $nomor = $this->numbers->generate($enrollment->batch->program, $tahun);
        $qrToken = Str::random(48);
        $qrUrl = route('verify.token', ['token' => $qrToken]);

        $pdf = $this->pdf->render($this->viewData($enrollment, $template, $nomor, $qrToken, $qrUrl));
        $path = $this->pdf->store($pdf, $nomor, $tahun);

        try {
            return DB::transaction(function () use ($enrollment, $template, $nomor, $qrToken, $path, $pdf, $actor): Certificate {
                $certificate = Certificate::create([
                    'enrollment_id' => $enrollment->getKey(),
                    'template_id' => $template->getKey(),
                    'nomor_sertifikat' => $nomor,
                    'qr_token' => $qrToken,
                    'file_path' => $path,
                    'file_hash' => hash('sha256', $pdf),
                    'status' => CertificateStatus::Aktif,
                    'tanggal_terbit' => now()->toDateString(),
                    'diterbitkan_oleh' => $actor?->getKey(),
                ]);

                AuditLogger::record('ISSUE_CERTIFICATE', $certificate, null, [
                    'nomor_sertifikat' => $certificate->nomor_sertifikat,
                    'enrollment_id' => $certificate->enrollment_id,
                    'file_path' => $certificate->file_path,
                ], $actor);

                return $certificate;
            });
        } catch (Throwable $exception) {
            $this->pdf->delete($path);

            throw $exception;
        }
    }

    /**
     * @return array{issued: int, skipped: int, failed: int, errors: array<int, string>}
     */
    public function issueBatch(Batch $batch, ?CertificateTemplate $template = null, ?User $actor = null): array
    {
        $template ??= $this->defaultTemplate();

        $result = [
            'issued' => 0,
            'skipped' => $batch->enrollments()
                ->where('status_kelulusan', GraduationStatus::Lulus->value)
                ->whereHas('certificates', fn ($query) => $query->where('status', CertificateStatus::Aktif->value))
                ->count(),
            'failed' => 0,
            'errors' => [],
        ];

        $batch->enrollments()
            ->with(['participant', 'batch.program'])
            ->where('status_kelulusan', GraduationStatus::Lulus->value)
            ->whereDoesntHave('certificates', fn ($query) => $query->where('status', CertificateStatus::Aktif->value))
            ->chunkById(50, function ($enrollments) use ($template, $actor, &$result): void {
                foreach ($enrollments as $enrollment) {
                    try {
                        $this->issue($enrollment, $template, $actor);
                        $result['issued']++;
                    } catch (Throwable $exception) {
                        $result['failed']++;
                        $result['errors'][$enrollment->getKey()] = $exception->getMessage();
                    }
                }
            });

        return $result;
    }

    private function defaultTemplate(): CertificateTemplate
    {
        $template = CertificateTemplate::query()
            ->where('is_active', true)
            ->orderByDesc('versi')
            ->first();

        if ($template === null) {
            throw ValidationException::withMessages([
                'template' => 'Belum ada template sertifikat aktif.',
            ]);
        }

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(
        Enrollment $enrollment,
        CertificateTemplate $template,
        string $nomor,
        string $qrToken,
        string $qrUrl,
    ): array {
        $program = $enrollment->batch->program;

        return [
            'nomor' => $nomor,
            'nama_peserta' => $enrollment->participant->nama_lengkap,
            'program' => $program->nama,
            'standar_kompetensi' => $program->standar_kompetensi,
            'batch' => $enrollment->batch->nama,
            'tahun' => $enrollment->batch->tahun,
            'tanggal_terbit' => now()->format('d-m-Y'),
            'qr_token' => $qrToken,
            'qr_url' => $qrUrl,
            'qr_data_uri' => $this->qr->dataUri($qrUrl),
            'signature_data_uri' => $this->signature->dataUri($template),
            'nama_penandatangan' => $this->signature->name($template),
            'jabatan_penandatangan' => $this->signature->title($template),
        ];
    }
}
