<?php

namespace App\Http\Controllers\Api;

use App\Enums\CertificateStatus;
use App\Enums\VerificationResult;
use App\Http\Controllers\Controller;
use App\Services\VerificationLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificateVerificationController extends Controller
{
    public function __construct(private VerificationLogger $logger) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor' => ['required', 'string', 'max:120'],
        ]);

        $nomor = trim($validated['nomor']);
        $certificate = DB::table('v_public_certificate')
            ->where('nomor_sertifikat', $nomor)
            ->first();

        $result = match (true) {
            $certificate === null => VerificationResult::TidakDitemukan,
            $certificate->status === CertificateStatus::Aktif->value => VerificationResult::Valid,
            default => VerificationResult::Dibatalkan,
        };

        $this->logger->log($nomor, $certificate?->certificate_id, $result);

        return response()->json([
            'hasil' => $result->value,
            'sertifikat' => $certificate === null ? null : [
                'nomor_sertifikat' => $certificate->nomor_sertifikat,
                'status' => $certificate->status,
                'nama' => $certificate->nama_lengkap,
                'program' => $certificate->program,
                'kode_batch' => $certificate->kode_batch,
                'tahun' => (int) $certificate->tahun,
                'tanggal_terbit' => $certificate->tanggal_terbit,
            ],
        ]);
    }
}
