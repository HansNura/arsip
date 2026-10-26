<?php

namespace App\Http\Controllers;

use App\Enums\CertificateStatus;
use App\Enums\GraduationStatus;
use App\Models\Batch;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Services\CertificateIssuer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCertificateController extends Controller
{
    public function __construct(private CertificateIssuer $issuer) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(CertificateStatus::class)],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
        ]);

        $certificates = Certificate::query()
            ->with(['enrollment.participant', 'enrollment.batch.program'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $query->where(function ($query) use ($filters): void {
                    $query->where('nomor_sertifikat', 'like', '%'.$filters['q'].'%')
                        ->orWhereHas('enrollment.participant', fn ($participant) => $participant->where('nama_lengkap', 'like', '%'.$filters['q'].'%'));
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['batch_id'] ?? null), fn ($query) => $query->whereHas(
                'enrollment',
                fn ($enrollment) => $enrollment->where('batch_id', $filters['batch_id']),
            ))
            ->latest('tanggal_terbit')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $batches = Batch::with('program')->orderByDesc('tahun')->orderBy('nama')->get();

        $selectedBatch = filled($filters['batch_id'] ?? null)
            ? $batches->firstWhere('id', (int) $filters['batch_id'])
            : null;

        $eligibleEnrollments = $selectedBatch
            ? Enrollment::with('participant')
                ->where('batch_id', $selectedBatch->id)
                ->where('status_kelulusan', GraduationStatus::Lulus->value)
                ->whereDoesntHave('certificates', fn ($query) => $query->where('status', CertificateStatus::Aktif->value))
                ->orderBy('id')
                ->get()
            : collect();

        return view('admin.certificates.index', [
            'certificates' => $certificates,
            'batches' => $batches,
            'filters' => $filters,
            'selectedBatch' => $selectedBatch,
            'eligibleEnrollments' => $eligibleEnrollments,
            'statuses' => CertificateStatus::cases(),
        ]);
    }

    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        try {
            $certificate = $this->issuer->issue($enrollment, null, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('status', 'Sertifikat '.$certificate->nomor_sertifikat.' berhasil diterbitkan.');
    }

    public function storeBatch(Request $request, Batch $batch): RedirectResponse
    {
        $result = $this->issuer->issueBatch($batch, null, $request->user());

        $redirect = back()->with('status', sprintf(
            '%d sertifikat diterbitkan, %d dilewati, %d gagal.',
            $result['issued'],
            $result['skipped'],
            $result['failed'],
        ));

        if ($result['errors'] !== []) {
            $redirect->withErrors(['certificate' => implode(' ', $result['errors'])]);
        }

        return $redirect;
    }

    public function storeBatchRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'integer', 'exists:batches,id'],
        ]);

        return $this->storeBatch($request, Batch::findOrFail($validated['batch_id']));
    }

    public function download(Certificate $certificate): StreamedResponse
    {
        $disk = Storage::disk((string) config('certificate.disk', 'local'));

        abort_unless(
            is_string($certificate->file_path) && $certificate->file_path !== '' && $disk->exists($certificate->file_path),
            404,
            'Berkas sertifikat tidak ditemukan.',
        );

        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', $certificate->nomor_sertifikat);

        return $disk->download(
            $certificate->file_path,
            'sertifikat-'.($name !== null && $name !== '' ? $name : $certificate->getKey()).'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
