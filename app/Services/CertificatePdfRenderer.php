<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

final class CertificatePdfRenderer
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function render(array $data): string
    {
        return Pdf::loadView('certificates.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->output();
    }

    public function store(string $pdf, string $nomorSertifikat, int $tahun): string
    {
        $path = sprintf('certificates/%d/%s.pdf', $tahun, $this->fileName($nomorSertifikat));

        $this->disk()->put($path, $pdf);

        return $path;
    }

    public function delete(string $path): void
    {
        $this->disk()->delete($path);
    }

    public function disk(): Filesystem
    {
        return Storage::disk((string) config('certificate.disk', 'local'));
    }

    private function fileName(string $nomorSertifikat): string
    {
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', $nomorSertifikat);

        if ($name === null || $name === '' || $name === '.' || $name === '..') {
            return 'sertifikat';
        }

        return $name;
    }
}
