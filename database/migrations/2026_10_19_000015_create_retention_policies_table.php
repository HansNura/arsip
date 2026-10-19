<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->enum('entity_type', ['participants', 'enrollments', 'certificates']);
            $table->enum('basis', ['tanggal_selesai_batch', 'tanggal_terbit_sertifikat', 'tanggal_daftar']);
            $table->unsignedInteger('retention_years')->default(5);
            $table->enum('action', ['arsipkan', 'cold_storage', 'hapus_lembut']);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_policies');
    }
};
