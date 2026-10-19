<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sertifikat_input');
            $table->foreignId('certificate_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('hasil', ['valid', 'dibatalkan', 'tidak_ditemukan']);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['ip_address', 'created_at']);
            $table->index(['nomor_sertifikat_input', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
