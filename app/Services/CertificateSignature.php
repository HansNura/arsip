<?php

namespace App\Services;

use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\Storage;

final class CertificateSignature
{
    public function dataUri(?CertificateTemplate $template = null): ?string
    {
        $path = $template?->tanda_tangan_path ?: config('certificate.signature_path');

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $disk = Storage::disk((string) config('certificate.disk', 'local'));

        if (! $disk->exists($path)) {
            return null;
        }

        $mime = $disk->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
    }

    public function name(?CertificateTemplate $template = null): ?string
    {
        return $this->value($template?->nama_penandatangan ?: config('certificate.penandatangan_nama'));
    }

    public function title(?CertificateTemplate $template = null): ?string
    {
        return $this->value($template?->jabatan_penandatangan ?: config('certificate.penandatangan_jabatan'));
    }

    private function value(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return $value;
    }
}
