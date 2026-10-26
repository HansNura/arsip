<?php

namespace App\Services;

use App\Models\Program;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class CertificateNumberGenerator
{
    private const PREFIX_PATTERN = '/^[A-Z0-9-]{1,20}$/';

    public function generate(Program $program, int $tahun, ?string $prefix = null): string
    {
        if ($tahun < 1900 || $tahun > 2200) {
            throw new InvalidArgumentException('Tahun sertifikat harus di antara 1900 dan 2200.');
        }

        $prefix = $this->normalizePrefix($prefix ?? $program->kode_program);
        $attempts = max(1, (int) config('certificate.max_attempts', 3));

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                [$storedPrefix, $number] = $this->allocate($program, $tahun, $prefix);

                return $this->format($storedPrefix, $tahun, $number);
            } catch (QueryException $exception) {
                if ($attempt === $attempts || ! $this->isUniqueViolation($exception)) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('Gagal mengalokasikan nomor sertifikat.');
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function allocate(Program $program, int $tahun, string $prefix): array
    {
        return DB::transaction(function () use ($program, $tahun, $prefix): array {
            $now = now();

            DB::table('certificate_number_sequences')->insertOrIgnore([
                'program_id' => $program->getKey(),
                'tahun' => $tahun,
                'prefix' => $prefix,
                'last_number' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $row = DB::selectOne(
                'UPDATE certificate_number_sequences
                 SET last_number = last_number + 1, updated_at = ?
                 WHERE program_id = ? AND tahun = ?
                 RETURNING prefix, last_number',
                [$now, $program->getKey(), $tahun],
            );

            if ($row === null) {
                throw new RuntimeException('Baris urutan nomor sertifikat tidak ditemukan.');
            }

            return [(string) $row->prefix, (int) $row->last_number];
        });
    }

    private function normalizePrefix(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));

        if (preg_match(self::PREFIX_PATTERN, $prefix) !== 1) {
            throw new InvalidArgumentException('Prefix nomor sertifikat hanya boleh berisi huruf, angka, dan tanda hubung (maksimal 20 karakter).');
        }

        return $prefix;
    }

    private function format(string $prefix, int $tahun, int $number): string
    {
        return strtr((string) config('certificate.number_format', '{prefix}/{tahun}/{urut:04d}'), [
            '{prefix}' => $prefix,
            '{tahun}' => (string) $tahun,
            '{urut:04d}' => str_pad((string) $number, 4, '0', STR_PAD_LEFT),
            '{urut}' => (string) $number,
        ]);
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true)
            || str_contains(strtolower($exception->getMessage()), 'unique');
    }
}
