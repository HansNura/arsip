<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->foreignId('template_id')->constrained('certificate_templates')->restrictOnDelete();
            $table->string('nomor_sertifikat')->unique();
            $table->string('qr_token', 64)->unique();
            $table->string('file_path')->nullable();
            $table->string('file_hash', 64);
            $table->enum('status', ['aktif', 'dibatalkan', 'diganti'])->default('aktif');
            $table->date('tanggal_terbit');
            $table->foreignId('diterbitkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dibatalkan_at')->nullable();
            $table->text('alasan_pembatalan')->nullable();
            $table->foreignId('replaced_by_id')->nullable()->constrained('certificates')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'tanggal_terbit']);
            $table->index('enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
