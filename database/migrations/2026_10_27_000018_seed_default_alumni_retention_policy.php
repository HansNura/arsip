<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('retention_policies')->insertOrIgnore([
            'nama' => 'Kebijakan Alumni 5 Tahun',
            'entity_type' => 'participants',
            'basis' => 'tanggal_selesai_batch',
            'retention_years' => 5,
            'action' => 'cold_storage',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('retention_policies')
            ->where('nama', 'Kebijakan Alumni 5 Tahun')
            ->where('entity_type', 'participants')
            ->delete();
    }
};
