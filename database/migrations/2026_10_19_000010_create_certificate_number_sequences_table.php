<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->string('prefix');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['program_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_number_sequences');
    }
};
