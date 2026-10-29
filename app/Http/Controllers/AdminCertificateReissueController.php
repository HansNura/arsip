<?php

namespace App\Http\Controllers;

use App\Enums\ReissueStatus;
use App\Models\Certificate;
use App\Models\CertificateReissue;
use App\Services\CertificateReissueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminCertificateReissueController extends Controller
{
    public function __construct(private CertificateReissueService $service) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(ReissueStatus::class)],
        ]);

        $reissues = CertificateReissue::query()
            ->with([
                'originalCertificate.enrollment.participant',
                'newCertificate',
                'diprosesOleh',
                'disetujuiOleh',
            ])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $query->where(function ($query) use ($filters): void {
                    $query->whereHas('originalCertificate', fn ($certificate) => $certificate->where('nomor_sertifikat', 'like', '%'.$filters['q'].'%'))
                        ->orWhereHas('originalCertificate.enrollment.participant', fn ($participant) => $participant->where('nama_lengkap', 'like', '%'.$filters['q'].'%'));
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->latest('created_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reissues.index', [
            'reissues' => $reissues,
            'filters' => $filters,
            'statuses' => ReissueStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.reissues.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_sertifikat' => ['required', 'string', 'max:120'],
            'alasan' => ['required', 'string', 'max:2000'],
            'pemohon_nama' => ['nullable', 'string', 'max:255'],
            'pemohon_kontak' => ['nullable', 'string', 'max:255'],
        ]);

        $certificate = Certificate::query()
            ->where('nomor_sertifikat', $validated['nomor_sertifikat'])
            ->first();

        if ($certificate === null) {
            return back()->withErrors([
                'nomor_sertifikat' => 'Nomor sertifikat tidak ditemukan.',
            ])->withInput();
        }

        try {
            $reissue = $this->service->create($certificate, $validated, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('admin.reissues.show', $reissue)
            ->with('status', 'Permohonan penerbitan ulang berhasil dibuat.');
    }

    public function show(CertificateReissue $reissue): View
    {
        $reissue->load([
            'originalCertificate.enrollment.participant',
            'originalCertificate.enrollment.batch.program',
            'newCertificate',
            'diprosesOleh',
            'disetujuiOleh',
        ]);

        return view('admin.reissues.show', compact('reissue'));
    }

    public function approve(Request $request, CertificateReissue $reissue): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_keputusan' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->service->approve($reissue, $request->user(), $validated['catatan_keputusan'] ?? null);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Permohonan disetujui.');
    }

    public function reject(Request $request, CertificateReissue $reissue): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_keputusan' => ['required', 'string', 'max:2000'],
        ], [
            'catatan_keputusan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            $this->service->reject($reissue, $request->user(), $validated['catatan_keputusan']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Permohonan ditolak.');
    }

    public function process(Request $request, CertificateReissue $reissue): RedirectResponse
    {
        try {
            $certificate = $this->service->complete($reissue, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.reissues.show', $reissue)
            ->with('status', 'Sertifikat pengganti '.$certificate->nomor_sertifikat.' berhasil diterbitkan.');
    }
}
