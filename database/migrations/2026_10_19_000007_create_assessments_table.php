<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->decimal('bobot', 5, 2);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['program_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
