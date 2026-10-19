<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_program')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->text('standar_kompetensi')->nullable();
            $table->unsignedInteger('durasi_jam')->nullable();
            $table->decimal('nilai_min_lulus', 5, 2)->default(70);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
