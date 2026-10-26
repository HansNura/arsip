<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Services\AuditLogger;
use App\Services\CertificateSignature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminCertificateTemplateController extends Controller
{
    private const AUDIT_FIELDS = [
        'tanda_tangan_path',
        'nama_penandatangan',
        'jabatan_penandatangan',
    ];

    public function __construct(private CertificateSignature $signature) {}

    public function edit(): View
    {
        $template = $this->activeTemplate();

        return view('admin.certificate-templates.edit', [
            'template' => $template,
            'signatureDataUri' => $this->signature->dataUri($template),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $template = $this->activeTemplate();

        $validated = $request->validate([
            'nama_penandatangan' => ['nullable', 'string', 'max:255'],
            'jabatan_penandatangan' => ['nullable', 'string', 'max:255'],
            'tanda_tangan' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'hapus_tanda_tangan' => ['sometimes', 'boolean'],
        ]);

        $disk = Storage::disk((string) config('certificate.disk', 'local'));
        $before = $template->only(self::AUDIT_FIELDS);

        $data = [
            'nama_penandatangan' => $validated['nama_penandatangan'] ?? null,
            'jabatan_penandatangan' => $validated['jabatan_penandatangan'] ?? null,
        ];

        if ($request->boolean('hapus_tanda_tangan') && $template->tanda_tangan_path) {
            $disk->delete($template->tanda_tangan_path);
            $data['tanda_tangan_path'] = null;
        }

        if ($request->hasFile('tanda_tangan')) {
            if ($template->tanda_tangan_path) {
                $disk->delete($template->tanda_tangan_path);
            }

            $data['tanda_tangan_path'] = $request->file('tanda_tangan')->store(
                'templates',
                (string) config('certificate.disk', 'local'),
            );
        }

        $template->update($data);

        AuditLogger::record(
            'UPDATE_CERTIFICATE_TEMPLATE',
            $template,
            $before,
            $template->fresh()->only(self::AUDIT_FIELDS),
        );

        return back()->with('status', 'Template dan tanda tangan berhasil diperbarui.');
    }

    private function activeTemplate(): CertificateTemplate
    {
        $template = CertificateTemplate::query()
            ->where('is_active', true)
            ->orderByDesc('versi')
            ->first();

        abort_if($template === null, 404, 'Belum ada template sertifikat aktif.');

        return $template;
    }
}
