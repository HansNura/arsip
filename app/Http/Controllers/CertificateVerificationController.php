<?php

namespace App\Http\Controllers;

use App\Enums\CertificateStatus;
use App\Enums\VerificationResult;
use App\Models\VerificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
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

    private function renderResult(string $nomor): View
    {
        $certificate = DB::table('v_public_certificate')
            ->where('nomor_sertifikat', $nomor)
            ->first();

        $result = match (true) {
            $certificate === null => VerificationResult::TidakDitemukan,
            $certificate->status === CertificateStatus::Aktif->value => VerificationResult::Valid,
            default => VerificationResult::Dibatalkan,
        };

        VerificationLog::create([
            'nomor_sertifikat_input' => $nomor,
            'certificate_id' => $certificate?->certificate_id,
            'hasil' => $result,
            'ip_address' => request()->ip() ?? 'unknown',
            'user_agent' => Str::limit((string) request()->userAgent(), 1000, ''),
            'created_at' => now(),
        ]);

        return view('verify.index', [
            'certificate' => $certificate,
            'result' => $result,
            'nomor' => $nomor,
        ]);
    }
}
