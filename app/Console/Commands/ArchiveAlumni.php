<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Models\RetentionPolicy;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ArchiveAlumni extends Command
{
    protected $signature = 'retention:archive-alumni';

    protected $description = 'Arsipkan atau soft-delete alumni yang melewati masa retensi aktif';

    public function handle(): int
    {
        $policies = RetentionPolicy::query()
            ->where('entity_type', 'participants')
            ->where('is_active', true)
            ->get();

        foreach ($policies as $policy) {
            $cutoff = now()->subYears($policy->retention_years)->toDateString();
            $count = 0;

            DB::transaction(function () use ($policy, $cutoff, &$count): void {
                $this->eligibleAlumni($cutoff)->chunkById(200, function ($participants) use ($policy, &$count): void {
                    foreach ($participants as $participant) {
                        if ($policy->action === 'hapus_lembut') {
                            $participant->delete();
                        } else {
                            $participant->forceFill([
                                'archived_at' => now(),
                                'storage_tier' => $policy->action === 'cold_storage' ? 'cold' : 'hot',
                            ])->save();
                        }

                        $count++;
                    }
                });
            });

            AuditLogger::record('RETENTION_ARCHIVE', $policy, null, [
                'action' => $policy->action,
                'retention_years' => $policy->retention_years,
                'cutoff' => $cutoff,
                'processed' => $count,
            ]);

            $this->info("Kebijakan {$policy->nama}: {$count} data alumni diproses.");
        }

        return self::SUCCESS;
    }

    /**
     * @return Builder<Participant>
     */
    private function eligibleAlumni(string $cutoff): Builder
    {
        return Participant::query()
            ->whereNull('archived_at')
            ->whereHas('enrollments', function ($query) use ($cutoff): void {
                $query->where('status_pendaftaran', 'selesai')
                    ->where('status_kelulusan', 'lulus')
                    ->whereHas('batch', fn ($batch) => $batch->whereDate('tanggal_selesai', '<', $cutoff));
            })
            ->whereDoesntHave('enrollments', function ($query) use ($cutoff): void {
                $query->where(function ($unfinished) use ($cutoff): void {
                    $unfinished->where('status_pendaftaran', '!=', 'selesai')
                        ->orWhere('status_kelulusan', '!=', 'lulus')
                        ->orWhereDoesntHave('batch', fn ($batch) => $batch->whereDate('tanggal_selesai', '<', $cutoff));
                });
            });
    }
}
