<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->string('nomor_peserta')->nullable();
            $table->enum('status_pendaftaran', ['terdaftar', 'aktif', 'mengundurkan_diri', 'selesai', 'dibatalkan'])
                ->default('terdaftar');
            $table->enum('status_kelulusan', ['belum_dinilai', 'lulus', 'tidak_lulus'])->default('belum_dinilai');
            $table->date('tanggal_daftar');
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['batch_id', 'status_kelulusan']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
