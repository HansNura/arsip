<?php

namespace App\Services;

use App\Enums\CertificateStatus;
use App\Enums\ReissueStatus;
use App\Models\Certificate;
use App\Models\CertificateReissue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

final class CertificateReissueService
{
    public function __construct(private CertificateIssuer $issuer) {}

    /**
     * @param  array{alasan: string, pemohon_nama?: string|null, pemohon_kontak?: string|null}  $data
     */
    public function create(Certificate $original, array $data, User $actor): CertificateReissue
    {
        if ($original->status !== CertificateStatus::Aktif) {
            throw ValidationException::withMessages([
                'nomor_sertifikat' => 'Hanya sertifikat aktif yang dapat diajukan penerbitan ulang.',
            ]);
        }

        $hasOpenRequest = $original->reissues()
            ->whereIn('status', [ReissueStatus::Diajukan->value, ReissueStatus::Disetujui->value])
            ->exists();

        if ($hasOpenRequest) {
            throw ValidationException::withMessages([
                'nomor_sertifikat' => 'Sudah ada permohonan reissue yang belum selesai untuk sertifikat ini.',
            ]);
        }

        $reissue = CertificateReissue::create([
            'original_certificate_id' => $original->getKey(),
            'alasan' => $data['alasan'],
            'pemohon_nama' => $data['pemohon_nama'] ?? null,
            'pemohon_kontak' => $data['pemohon_kontak'] ?? null,
            'status' => ReissueStatus::Diajukan,
            'diproses_oleh' => $actor->getKey(),
        ]);

        AuditLogger::record('CREATE_REISSUE', $reissue, null, [
            'original_certificate_id' => $original->getKey(),
            'nomor_sertifikat' => $original->nomor_sertifikat,
            'alasan' => $reissue->alasan,
        ], $actor);

        return $reissue;
    }

    public function approve(CertificateReissue $reissue, User $actor, ?string $note = null): void
    {
        $this->ensurePending($reissue);
        $this->ensureDifferentApprover($reissue, $actor);

        $before = $this->auditPayload($reissue);

        $reissue->update([
            'status' => ReissueStatus::Disetujui,
            'disetujui_oleh' => $actor->getKey(),
            'decided_at' => now(),
            'catatan_keputusan' => $note,
        ]);

        AuditLogger::record('APPROVE_REISSUE', $reissue, $before, $this->auditPayload($reissue->fresh()), $actor);
    }

    public function reject(CertificateReissue $reissue, User $actor, string $note): void
    {
        $this->ensurePending($reissue);
        $this->ensureDifferentApprover($reissue, $actor);

        $before = $this->auditPayload($reissue);

        $reissue->update([
            'status' => ReissueStatus::Ditolak,
            'disetujui_oleh' => $actor->getKey(),
            'decided_at' => now(),
            'catatan_keputusan' => $note,
        ]);

        AuditLogger::record('REJECT_REISSUE', $reissue, $before, $this->auditPayload($reissue->fresh()), $actor);
    }

    public function complete(CertificateReissue $reissue, User $actor): Certificate
    {
        if ($reissue->status !== ReissueStatus::Disetujui) {
            throw ValidationException::withMessages([
                'reissue' => 'Hanya permohonan yang sudah disetujui yang dapat diproses.',
            ]);
        }

        $reissue->loadMissing('originalCertificate.enrollment');
        $original = $reissue->originalCertificate;

        if ($original->status !== CertificateStatus::Aktif) {
            throw ValidationException::withMessages([
                'reissue' => 'Sertifikat asal sudah tidak aktif dan tidak dapat diganti.',
            ]);
        }

        $newCertificate = null;

        try {
            return DB::transaction(function () use ($reissue, $original, $actor, &$newCertificate): Certificate {
                $beforeOriginal = [
                    'status' => $original->status->value,
                    'replaced_by_id' => $original->replaced_by_id,
                ];

                $original->update(['status' => CertificateStatus::Diganti]);
                $newCertificate = $this->issuer->issue($original->enrollment, $original->template, $actor);
                $original->update(['replaced_by_id' => $newCertificate->getKey()]);

                $beforeReissue = $this->auditPayload($reissue);

                $reissue->update([
                    'new_certificate_id' => $newCertificate->getKey(),
                    'status' => ReissueStatus::Selesai,
                    'completed_at' => now(),
                ]);

                AuditLogger::record('REISSUE', $reissue, $beforeReissue, $this->auditPayload($reissue->fresh()), $actor);
                AuditLogger::record('REPLACE_CERTIFICATE', $original, $beforeOriginal, [
                    'status' => CertificateStatus::Diganti->value,
                    'replaced_by_id' => $newCertificate->getKey(),
                    'new_nomor_sertifikat' => $newCertificate->nomor_sertifikat,
                ], $actor);

                return $newCertificate;
            });
        } catch (Throwable $exception) {
            if ($newCertificate?->file_path) {
                Storage::disk((string) config('certificate.disk', 'local'))->delete($newCertificate->file_path);
            }

            throw $exception;
        }
    }

    private function ensurePending(CertificateReissue $reissue): void
    {
        if ($reissue->status !== ReissueStatus::Diajukan) {
            throw ValidationException::withMessages([
                'reissue' => 'Permohonan ini sudah diputuskan sebelumnya.',
            ]);
        }
    }

    private function ensureDifferentApprover(CertificateReissue $reissue, User $actor): void
    {
        if ($reissue->diproses_oleh === $actor->getKey()) {
            throw ValidationException::withMessages([
                'reissue' => 'Penyetuju harus berbeda dari staf yang mengajukan permohonan.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function auditPayload(CertificateReissue $reissue): array
    {
        return [
            'status' => $reissue->status->value,
            'new_certificate_id' => $reissue->new_certificate_id,
            'disetujui_oleh' => $reissue->disetujui_oleh,
            'catatan_keputusan' => $reissue->catatan_keputusan,
            'decided_at' => $reissue->decided_at?->toDateTimeString(),
            'completed_at' => $reissue->completed_at?->toDateTimeString(),
        ];
    }
}
