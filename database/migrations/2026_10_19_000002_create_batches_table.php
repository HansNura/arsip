<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->string('kode_batch')->unique();
            $table->string('nama');
            $table->unsignedSmallInteger('tahun');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedInteger('kuota')->nullable();
            $table->string('lokasi')->nullable();
            $table->enum('status', ['direncanakan', 'berjalan', 'selesai', 'dibatalkan'])->default('direncanakan');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_id', 'tahun']);
            $table->index(['tahun', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
