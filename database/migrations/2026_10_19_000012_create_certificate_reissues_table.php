<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_reissues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_certificate_id')->constrained('certificates')->restrictOnDelete();
            $table->foreignId('new_certificate_id')->nullable()->unique()->constrained('certificates')->nullOnDelete();
            $table->text('alasan');
            $table->string('pemohon_nama')->nullable();
            $table->string('pemohon_kontak')->nullable();
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak', 'selesai'])->default('diajukan');
            $table->foreignId('diproses_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->index('original_certificate_id');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_reissues');
    }
};
