<?php

namespace App\Http\Controllers;

use App\Enums\CertificateStatus;
use App\Enums\VerificationResult;
use App\Services\VerificationLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    public function __construct(private VerificationLogger $logger) {}

    public function index(Request $request): View
    {
        $nomor = $request->query('nomor');

        if (! is_string($nomor) || trim($nomor) === '') {
            return view('verify.index');
        }

        return $this->renderResult(trim($nomor));
    }

    public function check(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_sertifikat' => ['required', 'string', 'max:120'],
        ]);

        return redirect()->route('verify.index', [
            'nomor' => trim($validated['nomor_sertifikat']),
        ]);
    }

    public function showByToken(string $token): View
    {
        $certificate = DB::table('v_public_certificate')
            ->join('certificates', 'certificates.id', '=', 'v_public_certificate.certificate_id')
            ->where('certificates.qr_token', $token)
            ->select('v_public_certificate.*')
            ->first();

        $result = $this->resultFor($certificate);
        $nomor = $certificate?->nomor_sertifikat ?? '';

        $this->logger->log(
            $nomor !== '' ? $nomor : Str::limit($token, 120, ''),
            $certificate?->certificate_id,
            $result,
        );

        return view('verify.index', [
            'certificate' => $certificate,
            'result' => $result,
            'nomor' => $nomor,
            'viaQr' => true,
        ]);
    }

    private function renderResult(string $nomor): View
    {
        $certificate = DB::table('v_public_certificate')
            ->where('nomor_sertifikat', $nomor)
            ->first();

        $result = $this->resultFor($certificate);

        $this->logger->log($nomor, $certificate?->certificate_id, $result);

        return view('verify.index', [
            'certificate' => $certificate,
            'result' => $result,
            'nomor' => $nomor,
        ]);
    }

    private function resultFor(?object $certificate): VerificationResult
    {
        return match (true) {
            $certificate === null => VerificationResult::TidakDitemukan,
            $certificate->status === CertificateStatus::Aktif->value => VerificationResult::Valid,
            default => VerificationResult::Dibatalkan,
        };
    }
}
